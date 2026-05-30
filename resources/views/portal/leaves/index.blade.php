<x-portal-layout title="My Leave">
    <div class="space-y-6 pt-2">

        {{-- Balance cards --}}
        <div>
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Leave Balances — {{ now()->year }}</h3>
                <button
                    x-data
                    @click="$dispatch('open-modal', 'apply-leave')"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Apply for Leave
                </button>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                @foreach($leaveBalances as $balance)
                @php $pct = $balance['total'] > 0 ? round(($balance['used'] / $balance['total']) * 100) : 0; @endphp
                <div class="bg-white rounded-xl shadow-sm p-4">
                    <p class="text-xs text-gray-500 font-medium truncate">{{ $balance['name'] }}</p>
                    <div class="flex items-end justify-between mt-2">
                        <span class="text-2xl font-bold {{ $balance['remaining'] == 0 ? 'text-red-500' : 'text-gray-800' }}">{{ $balance['remaining'] }}</span>
                        <span class="text-xs text-gray-400">/ {{ $balance['total'] }}</span>
                    </div>
                    <div class="mt-2 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full {{ $pct >= 80 ? 'bg-red-400' : ($pct >= 50 ? 'bg-yellow-400' : 'bg-green-400') }}"
                             style="width: {{ $pct }}%"></div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">{{ $balance['used'] }} used · {{ $balance['total'] }} total</p>
                </div>
                @endforeach
            </div>
        </div>

        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Leave History</h3>

        {{-- History table --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Leave Type</th>
                        <th class="px-4 py-3 text-left">Period</th>
                        <th class="px-4 py-3 text-left">Days</th>
                        <th class="px-4 py-3 text-left">Reason</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($leaves as $leave)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $leave->leaveType->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $leave->start_date->format('d M Y') }} – {{ $leave->end_date->format('d M Y') }}</td>
                        <td class="px-4 py-3">{{ $leave->days_requested }}</td>
                        <td class="px-4 py-3 text-gray-400 text-xs max-w-xs truncate">{{ $leave->reason ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @php
                                $badge = [
                                    'pending'   => 'bg-yellow-100 text-yellow-800',
                                    'approved'  => 'bg-green-100 text-green-800',
                                    'rejected'  => 'bg-red-100 text-red-700',
                                    'cancelled' => 'bg-gray-100 text-gray-500',
                                ];
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs {{ $badge[$leave->status] ?? '' }}">{{ ucfirst($leave->status) }}</span>
                            @if($leave->status === 'rejected' && $leave->rejection_reason)
                                <p class="text-xs text-red-400 mt-0.5">{{ $leave->rejection_reason }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($leave->status === 'pending')
                                <form method="POST" action="{{ route('portal.leaves.cancel', $leave) }}"
                                      onsubmit="return confirm('Cancel this leave request?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-500 hover:text-red-700 hover:underline">Cancel</button>
                                </form>
                            @else
                                <span class="text-xs text-gray-300">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400">
                            No leave records yet.
                            <button x-data @click="$dispatch('open-modal', 'apply-leave')" class="text-blue-500 hover:underline ml-1">Apply now →</button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3">{{ $leaves->links() }}</div>
        </div>

    </div>

    {{-- Apply for Leave Modal --}}
    <x-modal name="apply-leave" maxWidth="lg" focusable>
        <form method="POST" action="{{ route('portal.leaves.store') }}" class="p-6 space-y-5">
            @csrf

            <h2 class="text-lg font-semibold text-gray-800">Apply for Leave</h2>

            {{-- Leave Type --}}
            <div>
                <x-input-label for="leave_type_id" value="Leave Type" />
                <select id="leave_type_id" name="leave_type_id" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                    <option value="">— Select leave type —</option>
                    @foreach($leaveTypes as $type)
                        <option value="{{ $type->id }}" {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('leave_type_id')" class="mt-1" />
            </div>

            {{-- Dates --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="start_date" value="Start Date" />
                    <x-text-input id="start_date" name="start_date" type="date"
                                  min="{{ now()->toDateString() }}"
                                  value="{{ old('start_date') }}"
                                  class="mt-1 block w-full" required />
                    <x-input-error :messages="$errors->get('start_date')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="end_date" value="End Date" />
                    <x-text-input id="end_date" name="end_date" type="date"
                                  min="{{ now()->toDateString() }}"
                                  value="{{ old('end_date') }}"
                                  class="mt-1 block w-full" required />
                    <x-input-error :messages="$errors->get('end_date')" class="mt-1" />
                </div>
            </div>

            {{-- Reason --}}
            <div>
                <x-input-label for="reason" value="Reason (optional)" />
                <textarea id="reason" name="reason" rows="3"
                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                          placeholder="Brief reason for your leave request...">{{ old('reason') }}</textarea>
                <x-input-error :messages="$errors->get('reason')" class="mt-1" />
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" x-data @click="$dispatch('close-modal', 'apply-leave')"
                        class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
                    Cancel
                </button>
                <button type="submit"
                        class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition">
                    Submit Request
                </button>
            </div>
        </form>
    </x-modal>

</x-portal-layout>
