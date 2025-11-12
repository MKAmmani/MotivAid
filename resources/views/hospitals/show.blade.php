@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Hospital Details</h1>
        <div>
            <a href="{{ route('hospitals.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 mr-2">Back to List</a>
            <a href="{{ route('hospitals.edit', $hospital) }}" class="px-4 py-2 bg-motivaid-teal text-white rounded-md hover:bg-motivaid-teal-dark">Edit</a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <div class="grid grid-cols-1 gap-4">
            <div>
                <h3 class="text-sm font-medium text-gray-500">Name</h3>
                <p class="mt-1 text-lg font-medium">{{ $hospital->name }}</p>
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-500">Location</h3>
                <p class="mt-1 text-lg font-medium">{{ $hospital->location ?? 'N/A' }}</p>
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-500">Contact Info</h3>
                <p class="mt-1 text-lg font-medium">{{ $hospital->contact_info ?? 'N/A' }}</p>
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-500">Created At</h3>
                <p class="mt-1 text-lg font-medium">{{ $hospital->created_at->format('M d, Y H:i') }}</p>
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-500">Updated At</h3>
                <p class="mt-1 text-lg font-medium">{{ $hospital->updated_at->format('M d, Y H:i') }}</p>
            </div>
        </div>
    </div>

    <div class="mt-8 bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Users at this Hospital</h2>
        
        @if($hospital->users->count() > 0)
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($hospital->users as $user)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $user->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $user->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $user->created_at->format('M d, Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-gray-500">No users assigned to this hospital.</p>
        @endif
    </div>
</div>
@endsection