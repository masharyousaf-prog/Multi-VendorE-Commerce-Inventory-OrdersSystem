@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold">User & Vendor Access Control</h1>
            <p class="text-xs opacity-70">All new registrations default to Approved (Active) status with JWT role claims.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 rounded-xl glass-panel text-xs font-bold">Back to Dashboard</a>
    </div>

    <div class="glass-panel rounded-3xl overflow-hidden">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-slate-500/20 bg-slate-800/30 uppercase tracking-wider text-[10px]">
                    <th class="p-4">User / Store</th>
                    <th class="p-4">Email</th>
                    <th class="p-4">JWT Role</th>
                    <th class="p-4">Account Status</th>
                    <th class="p-4 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-500/15">
                @foreach($users as $user)
                    <tr class="hover:bg-indigo-500/5 transition">
                        <td class="p-4 font-bold">
                            {{ $user->name }}
                            @if($user->store_name)
                                <span class="block text-[10px] text-orange-400">{{ $user->store_name }}</span>
                            @endif
                        </td>
                        <td class="p-4 opacity-80">{{ $user->email }}</td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase
                                {{ $user->role === 'admin' ? 'bg-purple-500/20 text-purple-400' : ($user->role === 'vendor' ? 'bg-orange-500/20 text-orange-400' : 'bg-cyan-500/20 text-cyan-400') }}">
                                {{ $user->role }}
                            </span>
                        </td>
                        <td class="p-4">
                            @if($user->is_active)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-500/15 text-emerald-400">
                                    <i class="fa-solid fa-circle-check mr-1"></i> Approved
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-rose-500/15 text-rose-400">
                                    <i class="fa-solid fa-ban mr-1"></i> Suspended
                                </span>
                            @endif
                        </td>
                        <td class="p-4 text-right">
                            @if($user->role !== 'admin')
                                <form method="POST" action="{{ route('admin.users.toggle', $user) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-3 py-1.5 rounded-xl font-bold text-[11px] {{ $user->is_active ? 'bg-rose-500/15 text-rose-400 hover:bg-rose-600 hover:text-white' : 'bg-emerald-500/15 text-emerald-400 hover:bg-emerald-600 hover:text-white' }} transition">
                                        {{ $user->is_active ? 'Suspend' : 'Approve' }}
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $users->links() }}
</div>
@endsection
