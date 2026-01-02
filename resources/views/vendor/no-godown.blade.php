@extends('layouts.vendor')

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 class="mt-4 text-lg font-medium text-gray-900">No Godown Assigned</h3>
            <p class="mt-2 text-gray-600">
                You don't have a godown assigned to your account yet. Please contact the administrator to get a godown
                assigned.
            </p>
            <div class="mt-6">
                <p class="text-sm text-gray-500">
                    Your account: {{ $user->name }} ({{ $user->email }})
                </p>
            </div>
        </div>
    </div>
@endsection
