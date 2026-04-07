import lighthouse from 'lighthouse';
import * as chromeLauncher from 'chrome-launcher';
import fs from 'node:fs';
import net from 'node:net';
import { execFileSync, execSync } from 'node:child_process';

const url = process.argv[2];

if (!url) {
    console.error('URL is required.');
    process.exit(1);
}

const categories = ['performance', 'accessibility', 'best-practices', 'seo'];
const debugPortBase = Number.parseInt(process.env.LIGHTHOUSE_CHROME_PORT ?? '9222', 10);
const chromeFlags = [
    '--headless=new',
    '--no-sandbox',
    '--disable-setuid-sandbox',
    '--disable-gpu',
    '--disable-dev-shm-usage',
    '--no-zygote',
    '--no-first-run',
    '--remote-debugging-address=127.0.0.1',
];

async function runForFormFactor(formFactor) {
    let chrome;

    try {
        const chromePath = resolveChromePath();
        const debugPort = formFactor === 'mobile' ? debugPortBase + 1 : debugPortBase;
        chrome = await chromeLauncher.launch({ chromeFlags, chromePath, port: debugPort });

        await waitForDebugPort(chrome.port);

        const result = await lighthouse(
            url,
            {
                output: 'json',
                logLevel: 'error',
                onlyCategories: categories,
                port: chrome.port,
                emulatedFormFactor: formFactor,
            },
        );

        const lhr = result?.lhr;

        if (!lhr) {
            throw new Error('Lighthouse did not return a report.');
        }

        return {
            formFactor,
            ok: true,
            data: {
                performance_score: lhr.categories?.performance?.score ?? null,
                accessibility_score: lhr.categories?.accessibility?.score ?? null,
                best_practices_score: lhr.categories?.['best-practices']?.score ?? null,
                seo_score: lhr.categories?.seo?.score ?? null,
                fcp_ms: lhr.audits?.['first-contentful-paint']?.numericValue ?? null,
                lcp_ms: lhr.audits?.['largest-contentful-paint']?.numericValue ?? null,
                cls: lhr.audits?.['cumulative-layout-shift']?.numericValue ?? null,
                tbt_ms: lhr.audits?.['total-blocking-time']?.numericValue ?? null,
                speed_index_ms: lhr.audits?.['speed-index']?.numericValue ?? null,
                raw_json: lhr,
            },
        };
    } catch (error) {
        return {
            formFactor,
            ok: false,
            error: error instanceof Error ? error.message : String(error),
        };
    } finally {
        if (chrome) {
            await chrome.kill();
        }
    }
}

const desktop = await runForFormFactor('desktop');
const mobile = await runForFormFactor('mobile');

console.log(JSON.stringify({ desktop, mobile }));

async function waitForDebugPort(port, attempts = 20, delayMs = 250) {
    for (let i = 0; i < attempts; i += 1) {
        const ok = await canConnect(port);

        if (ok) {
            return;
        }

        await sleep(delayMs);
    }

    throw new Error(`Chrome debug port is not reachable on 127.0.0.1:${port}.`);
}

function canConnect(port) {
    return new Promise((resolve) => {
        const socket = new net.Socket();

        socket.setTimeout(500);
        socket.once('connect', () => {
            socket.destroy();
            resolve(true);
        });
        socket.once('timeout', () => {
            socket.destroy();
            resolve(false);
        });
        socket.once('error', () => {
            socket.destroy();
            resolve(false);
        });

        socket.connect(port, '127.0.0.1');
    });
}

function sleep(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
}

function resolveChromePath() {
    const envPath = process.env.LIGHTHOUSE_CHROME_PATH || process.env.CHROME_PATH;

    if (envPath && fs.existsSync(envPath) && isUsableChromeBinary(envPath)) {
        return envPath;
    }

    const pathCandidates = [
        '/usr/bin/chromium',
        '/usr/bin/chromium-browser',
        '/usr/bin/google-chrome',
        '/usr/bin/google-chrome-stable',
        '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    ];

    for (const candidate of pathCandidates) {
        if (fs.existsSync(candidate) && isUsableChromeBinary(candidate)) {
            return candidate;
        }
    }

    const playwrightPath = findPlaywrightChromiumPath();
    if (playwrightPath && isUsableChromeBinary(playwrightPath)) {
        return playwrightPath;
    }

    const binaryCandidates = ['chromium', 'chromium-browser', 'google-chrome', 'google-chrome-stable'];

    for (const binary of binaryCandidates) {
        const resolved = which(binary);

        if (resolved && isUsableChromeBinary(resolved)) {
            return resolved;
        }
    }

    throw new Error(
        'Chrome/Chromium not found. Install Chromium in the container and set CHROME_PATH (example: /usr/bin/chromium).',
    );
}

function which(binary) {
    try {
        const output = execSync(`command -v ${binary}`, { stdio: ['ignore', 'pipe', 'ignore'] })
            .toString()
            .trim();

        return output || null;
    } catch {
        return null;
    }
}

function findPlaywrightChromiumPath() {
    const roots = ['/ms-playwright', '/root/.cache/ms-playwright', '/home/sail/.cache/ms-playwright'];

    for (const root of roots) {
        if (!fs.existsSync(root)) {
            continue;
        }

        const result = findFirst([
            root,
            '-type',
            'f',
            '(',
            '-path',
            '*/chrome-linux/chrome',
            '-o',
            '-path',
            '*/chrome-linux64/chrome',
            ')',
        ]);

        if (result) {
            return result;
        }
    }

    return null;
}

function findFirst(args) {
    try {
        const output = execFileSync('find', args, { stdio: ['ignore', 'pipe', 'ignore'] })
            .toString()
            .trim()
            .split('\n')
            .filter(Boolean);

        return output[0] ?? null;
    } catch {
        return null;
    }
}

function isUsableChromeBinary(binaryPath) {
    try {
        execFileSync(binaryPath, ['--version'], { stdio: ['ignore', 'pipe', 'pipe'] });
        return true;
    } catch {
        return false;
    }
}
