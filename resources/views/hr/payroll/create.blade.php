<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Run Payroll</h2></x-slot>

    <div class="py-8 max-w-md mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg p-6">
            <p class="text-sm text-gray-600 mb-4">This will compute PAYE, NSSF, NHIF, and Housing Levy for all active employees for the selected month. A payroll run cannot be created twice for the same month.</p>

            <form method="POST" action="{{ route('hr.payroll.store') }}" class="space-y-4">
                @csrf

                @if($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded text-sm">
                        @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                    </div>
                @endif

                <div>
                    <x-input-label for="month" value="Month *" />
                    <select id="month" name="month" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        @foreach(range(1,12) as $m)
                            <option value="{{ $m }}" {{ old('month', now()->month) == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-input-label for="year" value="Year *" />
                    <select id="year" name="year" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        @foreach(range(now()->year - 1, now()->year + 1) as $y)
                            <option value="{{ $y }}" {{ old('year', now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-3 pt-2">
                    <x-primary-button>Process Payroll</x-primary-button>
                    <a href="{{ route('hr.payroll.index') }}" class="text-sm text-gray-500 hover:underline self-center">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
