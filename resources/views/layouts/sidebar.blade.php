<aside class="hidden lg:block w-80 rounded-[2rem] border border-slate-200/70 bg-white/95 shadow-2xl shadow-slate-200/40 overflow-hidden">
    <div class="sticky top-0 h-screen overflow-y-auto pt-6 pb-6">
        <div class="px-6 pb-5 border-b border-slate-200/80">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-[1.25rem] bg-gradient-to-br from-sky-500 to-indigo-600 text-white shadow-lg shadow-sky-500/20">IL</span>
                <div>
                    <div class="text-lg font-semibold text-slate-900">Imara Logic</div>
                    <p class="text-xs uppercase tracking-[0.22em] text-slate-500">HR Payroll</p>
                </div>
            </a>
            <p class="mt-3 text-sm text-slate-500">Smart, compliant HR operations.</p>
        </div>

        <nav class="px-4 py-4 space-y-4">
            <div>
                <p class="px-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Main</p>
                <div class="mt-3 space-y-1">
                    <a href="{{ route('dashboard') }}"
                       class="sidebar-link {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700' : 'text-slate-700' }}">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-slate-100 text-slate-600 group-hover:bg-slate-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l7-7 7 7M5 10v10h14V10"/></svg>
                        </span>
                        Home
                    </a>
                </div>
            </div>

            <div>
                <p class="px-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Workforce</p>
                <div class="mt-3 space-y-1">
                    <a href="{{ route('hr.employees.index') }}"
                       class="sidebar-link {{ request()->is('hr/employees*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700' }}">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-slate-100 text-slate-600 group-hover:bg-slate-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </span>
                        Employees
                    </a>
                    <a href="{{ route('hr.departments.index') }}"
                       class="sidebar-link {{ request()->is('hr/departments*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700' }}">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-slate-100 text-slate-600 group-hover:bg-slate-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </span>
                        Organization
                    </a>
                </div>
            </div>

            <div>
                <p class="px-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Payroll & finance</p>
                <div class="mt-3 space-y-1">
                    <a href="{{ route('hr.payroll.index') }}"
                       class="sidebar-link {{ request()->is('hr/payroll*') && ! request()->routeIs('hr.payroll.create') ? 'bg-blue-50 text-blue-700' : 'text-slate-700' }}">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-slate-100 text-slate-600 group-hover:bg-slate-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 01-2-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </span>
                        Payroll Runs
                    </a>
                    <a href="{{ route('hr.payroll.create') }}"
                       class="sidebar-link {{ request()->routeIs('hr.payroll.create') ? 'bg-blue-50 text-blue-700' : 'text-slate-700' }}">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-slate-100 text-slate-600 group-hover:bg-slate-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </span>
                        Run Payroll
                    </a>
                </div>
            </div>

            <div>
                <p class="px-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Time & benefits</p>
                <div class="mt-3 space-y-1">
                    <a href="{{ route('hr.leaves.index') }}"
                       class="sidebar-link {{ request()->is('hr/leaves*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700' }}">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-slate-100 text-slate-600 group-hover:bg-slate-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </span>
                        Leave Requests
                    </a>
                    <a href="{{ route('hr.leave-types.index') }}"
                       class="sidebar-link {{ request()->is('hr/leave-types*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700' }}">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-slate-100 text-slate-600 group-hover:bg-slate-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </span>
                        Leave Types
                    </a>
                    <a href="{{ route('hr.attendance.index') }}"
                       class="sidebar-link {{ request()->is('hr/attendance*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700' }}">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-slate-100 text-slate-600 group-hover:bg-slate-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        Attendance
                    </a>
                    <a href="{{ route('hr.loans.index') }}"
                       class="sidebar-link {{ request()->is('hr/loans*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700' }}">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-slate-100 text-slate-600 group-hover:bg-slate-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        Loans & Advances
                    </a>
                </div>
            </div>

            <div>
                <p class="px-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Compliance</p>
                <div class="mt-3 space-y-1">
                    <a href="{{ route('hr.statutory-rates.index') }}"
                       class="sidebar-link {{ request()->is('hr/statutory-rates*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700' }}">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-slate-100 text-slate-600 group-hover:bg-slate-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-3-3v6m-4 5h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </span>
                        Statutory Rates
                    </a>
                    <a href="{{ route('hr.kra-exports.index') }}"
                       class="sidebar-link {{ request()->is('hr/kra-exports*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700' }}">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-slate-100 text-slate-600 group-hover:bg-slate-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6a2 2 0 012-2h2a2 2 0 012 2v6m-6 0h6M5 18h14M5 6h14M5 6l7 6 7-6"/></svg>
                        </span>
                        KRA Exports
                    </a>
                    @role('Super Admin')
                    <a href="{{ route('hr.audit-logs.index') }}"
                       class="sidebar-link {{ request()->is('hr/audit-logs*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700' }}">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-slate-100 text-slate-600 group-hover:bg-slate-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6a2 2 0 012-2h2a2 2 0 012 2v6m-6 0h6M5 18h14M5 6h14M5 6l7 6 7-6"/></svg>
                        </span>
                        Audit Logs
                    </a>
                    @endrole
                </div>
            </div>

            <div>
                <p class="px-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Analytics</p>
                <div class="mt-3 space-y-1">
                    <a href="{{ route('hr.reports.payroll') }}"
                       class="sidebar-link {{ request()->routeIs('hr.reports.payroll') ? 'bg-blue-50 text-blue-700' : 'text-slate-700' }}">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-slate-100 text-slate-600 group-hover:bg-slate-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6a2 2 0 011.858-2.528L14 8l.142.014A2 2 0 0116 10v6"/></svg>
                        </span>
                        Payroll Report
                    </a>
                    <a href="{{ route('hr.reports.leave') }}"
                       class="sidebar-link {{ request()->routeIs('hr.reports.leave') ? 'bg-blue-50 text-blue-700' : 'text-slate-700' }}">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-slate-100 text-slate-600 group-hover:bg-slate-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6a2 2 0 011.858-2.528L14 8l.142.014A2 2 0 0116 10v6"/></svg>
                        </span>
                        Leave Report
                    </a>
                    <a href="{{ route('hr.advanced-reports.dashboard') }}"
                       class="sidebar-link {{ request()->is('hr/advanced-reports*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700' }}">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-slate-100 text-slate-600 group-hover:bg-slate-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18M9 8h6M9 12h6M9 16h6"/></svg>
                        </span>
                        Advanced Reports
                    </a>
                </div>
            </div>
        </nav>
    </div>
</aside>
