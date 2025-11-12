@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Hospitals</h1>
        <a href="{{ route('hospitals.create') }}" class="bg-motivaid-teal text-white px-4 py-2 rounded-md hover:bg-motivaid-teal-dark">Add New Hospital</a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact Info</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($hospitals as $hospital)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $hospital->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $hospital->location }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $hospital->contact_info }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $hospital->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('hospitals.show', $hospital) }}" class="text-blue-600 hover:text-blue-900 mr-2">View</a>
                            <a href="{{ route('hospitals.edit', $hospital) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">Edit</a>
                            <form action="{{ route('hospitals.destroy', $hospital) }}" method="POST" class="inline-block" 
                                  onsubmit="return confirm('Are you sure you want to delete this hospital?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">No hospitals found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection