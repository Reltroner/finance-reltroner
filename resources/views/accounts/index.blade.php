{{-- resources/views/accounts/index.blade.php --}}

@extends('layouts.app')

@section('title', 'Accounts List')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Accounts</h1>
        <a href="{{ route('accounts.create') }}"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow transition">
            + New Account
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full border rounded-xl shadow bg-white">
            <thead>
                <tr class="bg-gray-100">
                    <th class="py-2 px-4 text-left">Code</th>
                    <th class="py-2 px-4 text-left">Name</th>
                    <th class="py-2 px-4 text-left">Type</th>
                    <th class="py-2 px-4 text-left">Parent</th>
                    <th class="py-2 px-4 text-center">Active</th>
                    <th class="py-2 px-4 text-center">Budgets</th>
                    <th class="py-2 px-4 text-center">Children</th>
                    <th class="py-2 px-4 text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($accounts as $account)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-2 px-4 font-mono">{{ $account->code }}</td>
                        <td class="py-2 px-4">{{ $account->name }}</td>
                        <td class="py-2 px-4 capitalize">{{ $account->type }}</td>
                        <td class="py-2 px-4">
                            {{ $account->parent ? $account->parent->name : '-' }}
                        </td>
                        <td class="py-2 px-4 text-center">
                            @if($account->is_active)
                                <span class="inline-block w-3 h-3 bg-green-500 rounded-full"></span>
                            @else
                                <span class="inline-block w-3 h-3 bg-red-400 rounded-full"></span>
                            @endif
                        </td>
                        <td class="py-2 px-4 text-center">
                            {{ $account->budgets->count() }}
                        </td>
                        <td class="py-2 px-4 text-center">
                            {{ $account->children->count() }}
                        </td>
                        <td class="py-2 px-4 text-center">
                            <a href="{{ route('accounts.show', $account) }}"
                               class="text-blue-600 hover:underline">View</a>
                            <a href="{{ route('accounts.edit', $account) }}"
                               class="text-yellow-600 hover:underline ml-2">Edit</a>
                            <form action="{{ route('accounts.destroy', $account) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" onclick="return confirm('Delete this account?')"
                                    class="text-red-600 hover:underline ml-2">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-gray-500">No accounts found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $accounts->links() }}
    </div>
</div>
@endsection
