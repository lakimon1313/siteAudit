import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Create() {
    const { data, setData, post, processing, errors } = useForm({
        url: '',
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('websites.store'));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Add Website
                </h2>
            }
        >
            <Head title="Add Website" />

            <div className="py-12">
                <div className="mx-auto max-w-3xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                        <form onSubmit={submit}>
                            <div>
                                <InputLabel htmlFor="url" value="Website URL" />

                                <TextInput
                                    id="url"
                                    type="url"
                                    name="url"
                                    value={data.url}
                                    className="mt-1 block w-full"
                                    onChange={(e) => setData('url', e.target.value)}
                                    placeholder="https://example.com"
                                    isFocused
                                />

                                <InputError
                                    message={errors.url}
                                    className="mt-2"
                                />
                            </div>

                            <div className="mt-6 flex items-center gap-4">
                                <PrimaryButton disabled={processing}>
                                    Save
                                </PrimaryButton>
                                <Link
                                    href={route('websites.index')}
                                    className="text-sm text-gray-600 hover:text-gray-900"
                                >
                                    Cancel
                                </Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
