<x-portal-layout title="Apply for Leave">
    <div class="max-w-2xl space-y-6 pt-2">

        {{-- Quick balance reference --}}
        <div class="grid grid-cols-3 gap-3">
            @foreach(array_slice($leaveBalances, 0, 3) as $b)
            <div class="bg-white rounded-xl p-3 shadow-sm text-center">
                <p class="text-xs text-gray-400 truncate">{{ $b['name'] }}</p>
                <p class="text-xl font-bold {{ $b['remaining'] == 0 ? 'text-red-500' : 'text-blue-700' }}">{{ $b['remaining'] }}</p>
                <p class="text-xs text-gray-400">days left</p>
            </div>
            @endforeach
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-700 mb-5">Leave Application Form</h3>

            <form method="POST" action="{{ route('portal.leaves.store') }}" class="space-y-5" id="leaveForm">
                @csrf

                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                        @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Leave Type *</label>
                    <select name="leave_type_id" id="leaveTypeId" class="block w-full border-gray-300 rounded-lg shadow-sm text-sm" required>
                        <option value="">-- Select leave type --</option>
                        @foreach($leaveTypes as $lt)
                            @php $bal = collect($leaveBalances)->firstWhere('id', $lt->id); @endphp
                            <option value="{{ $lt->id }}" {{ old('leave_type_id') == $lt->id ? 'selected' : '' }}
                                {{ $bal && $bal['remaining'] == 0 ? 'disabled' : '' }}>
                                {{ $lt->name }} ({{ $bal ? $bal['remaining'] : $lt->days_per_year }} days remaining)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Start Date *</label>
                        <input type="date" name="start_date" id="startDate" value="{{ old('start_date') }}"
                               min="{{ now()->toDateString() }}"
                               class="block w-full border-gray-300 rounded-lg shadow-sm text-sm px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">End Date *</label>
                        <input type="date" name="end_date" id="endDate" value="{{ old('end_date') }}"
                               min="{{ now()->toDateString() }}"
                               class="block w-full border-gray-300 rounded-lg shadow-sm text-sm px-3 py-2" required>
                    </div>
                </div>

                {{-- Working days counter --}}
                <div id="daysCounter" class="hidden bg-blue-50 border border-blue-200 rounded-lg px-4 py-2 text-sm text-blue-700">
                    Working days requested: <strong id="daysCount">0</strong>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Reason (optional)</label>
                    <textarea name="reason" rows="3" class="block w-full border-gray-300 rounded-lg shadow-sm text-sm px-3 py-2" placeholder="Briefly describe the reason for leave…">{{ old('reason') }}</textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button class="bg-blue-600 text-white px-5 py-2 rounded-lg text-sm hover:bg-blue-700">Submit Request</button>
                    <a href="{{ route('portal.leaves.index') }}" class="text-sm text-gray-500 hover:underline self-center">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function countWorkingDays(start, end) {
            let count = 0;
            let cur = new Date(start);
            const last = new Date(end);
            while (cur <= last) {
                const d = cur.getDay();
                if (d !== 0 && d !== 6) count++;
                cur.setDate(cur.getDate() + 1);
            }
            return count;
        }

        function updateDays() {
            const s = document.getElementById('startDate').value;
            const e = document.getElementById('endDate').value;
            const counter = document.getElementById('daysCounter');
            const countEl = document.getElementById('daysCount');
            if (s && e && e >= s) {
                const d = countWorkingDays(s, e);
                countEl.textContent = d;
                counter.classList.remove('hidden');
            } else {
                counter.classList.add('hidden');
            }
        }

        document.getElementById('startDate').addEventListener('change', function() {
            document.getElementById('endDate').min = this.value;
            updateDays();
        });
        document.getElementById('endDate').addEventListener('change', updateDays);
    </script>
</x-portal-layout>
