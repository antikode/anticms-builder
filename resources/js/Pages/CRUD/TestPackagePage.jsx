import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function TestPackagePage() {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Test Package Page - From Package Directory
                </h2>
            }
        >
            <Head title="Test Package Page" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <h3 className="text-lg font-medium mb-4">Package Page Resolution Test</h3>
                            <p className="mb-4">
                                This page is loaded from the package's vendor directory.
                            </p>
                            <div className="bg-green-50 border border-green-200 rounded-md p-4">
                                <p className="text-green-800">
                                    ✅ Package page resolution is working correctly!
                                </p>
                                <p className="text-green-700 mt-2 text-sm">
                                    Path: resources/js/vendor/anti-cms-builder/Pages/CRUD/TestPackagePage.jsx
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}