@extends('layouts.app')

@section('title', 'Dashboard Tenants | Kost Management')

@section('content')
    <!-- Page Header & Action -->
    <div class="page-header">
        <h1 class="page-title">Tenant Dashboard</h1>
        <button type="button" class="btn btn-primary" onclick="openAddModal()">+ Add Tenant</button>
    </div>

    <!-- Summary Stats Section -->
    <section class="summary-grid">
        <div class="summary-card">
            <span class="summary-label">Total Tenants</span>
            <span class="summary-value">{{ $tenants->count() }}</span>
        </div>
        <div class="summary-card">
            <span class="summary-label">Paid This Month</span>
            <span class="summary-value">{{ $tenants->filter(fn($t) => $t->status === 'paid')->count() }}</span>
        </div>
        <div class="summary-card">
            <span class="summary-label">Unpaid This Month</span>
            <span class="summary-value">{{ $tenants->filter(fn($t) => $t->status === 'unpaid')->count() }}</span>
        </div>
        <div class="summary-card">
            <span class="summary-label">Overdue Payments</span>
            <span class="summary-value">{{ $tenants->filter(fn($t) => $t->status === 'overdue')->count() }}</span>
        </div>
    </section>

    <!-- Tenant Management Section -->
    <section class="section-card">
        <div class="section-title-bar">
            <h2 class="section-title">Active Tenant Directory</h2>
            <span class="summary-label" style="margin-bottom: 0;">Managing {{ $tenants->count() }} Rooms</span>
        </div>

        @if ($tenants->isEmpty())
            <div style="padding: var(--spacing-xxl); text-align: center; color: var(--color-text-muted);">
                <p>No tenant records found. Click "+ Add Tenant" to create one.</p>
            </div>
        @else
            <!-- Desktop Table View -->
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 8%;">Room</th>
                            <th style="width: 20%;">Tenant Name</th>
                            <th style="width: 12%;">Phone Number</th>
                            <th style="width: 12%;">Monthly Rent</th>
                            <th style="width: 18%;">Period</th>
                            <th style="width: 8%;">Due Day</th>
                            <th style="width: 10%;">Status</th>
                            <th style="width: 12%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tenants as $tenant)
                            <tr>
                                <td style="font-weight: 700;">{{ $tenant->room_number }}</td>
                                <td>{{ $tenant->name }}</td>
                                <td>{{ $tenant->phone_number }}</td>
                                <td>{{ $tenant->formatted_rent }}</td>
                                <td>
                                    <div>In: {{ $tenant->move_in_date ? $tenant->move_in_date->format('d M Y') : '-' }}</div>
                                    <div style="font-size: 0.75rem; color: var(--color-text-muted);">
                                        Out: {{ $tenant->move_out_date ? $tenant->move_out_date->format('d M Y') : '-' }}
                                    </div>
                                </td>
                                <td>Day {{ $tenant->due_day }}</td>
                                <td>
                                    @if ($tenant->status === 'paid')
                                        <span class="badge badge-paid">✓ Paid</span>
                                    @elseif ($tenant->status === 'unpaid')
                                        <span class="badge badge-unpaid">○ Unpaid</span>
                                    @else
                                        <span class="badge badge-overdue">! Overdue</span>
                                    @endif
                                </td>
                                <td class="actions">
                                    <button type="button" class="btn btn-sm" 
                                            data-id="{{ $tenant->id }}"
                                            data-name="{{ $tenant->name }}"
                                            data-phone="{{ $tenant->phone_number }}"
                                            data-room="{{ $tenant->room_number }}"
                                            data-rent="{{ $tenant->monthly_rent }}"
                                            data-due="{{ $tenant->due_day }}"
                                            data-move-in="{{ $tenant->move_in_date ? $tenant->move_in_date->format('Y-m-d') : '' }}"
                                            data-move-out="{{ $tenant->move_out_date ? $tenant->move_out_date->format('Y-m-d') : '' }}"
                                            onclick="openEditModal(this)">
                                        Edit
                                    </button>
                                    <button type="button" class="btn btn-sm"
                                            data-id="{{ $tenant->id }}"
                                            data-name="{{ $tenant->name }}"
                                            data-status="{{ $tenant->status }}"
                                            data-history="{{ json_encode($tenant->transactions) }}"
                                            onclick="openHistoryModal(this)">
                                        History
                                    </button>
                                    <form action="{{ route('tenants.destroy', $tenant->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tenant ini?');" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger-outline">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card/List View -->
            <div class="mobile-card-list">
                @foreach ($tenants as $tenant)
                    <div class="mobile-card">
                        <div class="mobile-card-header">
                            <div>
                                <span class="mobile-card-room">Room {{ $tenant->room_number }}</span>
                                <div style="font-weight: 500; font-size: 1rem; margin-top: var(--spacing-xs);">{{ $tenant->name }}</div>
                            </div>
                            <div>
                                @if ($tenant->status === 'paid')
                                    <span class="badge badge-paid">✓ Paid</span>
                                @elseif ($tenant->status === 'unpaid')
                                    <span class="badge badge-unpaid">○ Unpaid</span>
                                @else
                                    <span class="badge badge-overdue">! Overdue</span>
                                @endif
                            </div>
                        </div>

                        <div class="mobile-card-body">
                            <div>
                                <div class="mobile-card-label">WhatsApp</div>
                                <div class="mobile-card-val">{{ $tenant->phone_number }}</div>
                            </div>
                            <div>
                                <div class="mobile-card-label">Rent Rate</div>
                                <div class="mobile-card-val">{{ $tenant->formatted_rent }}</div>
                            </div>
                            <div>
                                <div class="mobile-card-label">Monthly Due Date</div>
                                <div class="mobile-card-val">Day {{ $tenant->due_day }}</div>
                            </div>
                            <div>
                                <div class="mobile-card-label">Period of Stay</div>
                                <div class="mobile-card-val" style="font-size: 0.8rem;">
                                    {{ $tenant->move_in_date ? $tenant->move_in_date->format('d M Y') : '-' }} to
                                    {{ $tenant->move_out_date ? $tenant->move_out_date->format('d M Y') : '-' }}
                                </div>
                            </div>
                        </div>

                        <div class="mobile-card-actions">
                            <button type="button" class="btn btn-sm"
                                    data-id="{{ $tenant->id }}"
                                    data-name="{{ $tenant->name }}"
                                    data-phone="{{ $tenant->phone_number }}"
                                    data-room="{{ $tenant->room_number }}"
                                    data-rent="{{ $tenant->monthly_rent }}"
                                    data-due="{{ $tenant->due_day }}"
                                    data-move-in="{{ $tenant->move_in_date ? $tenant->move_in_date->format('Y-m-d') : '' }}"
                                    data-move-out="{{ $tenant->move_out_date ? $tenant->move_out_date->format('Y-m-d') : '' }}"
                                    onclick="openEditModal(this)">
                                Edit
                            </button>
                            <button type="button" class="btn btn-sm"
                                    data-id="{{ $tenant->id }}"
                                    data-name="{{ $tenant->name }}"
                                    data-status="{{ $tenant->status }}"
                                    data-history="{{ json_encode($tenant->transactions) }}"
                                    onclick="openHistoryModal(this)">
                                History
                            </button>
                            <form action="{{ route('tenants.destroy', $tenant->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tenant ini?');" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger-outline" style="width: 100%;">Delete</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <!-- Dialog Add Tenant -->
    <dialog id="add-tenant-modal">
        <div class="dialog-header">
            <h3 class="dialog-title">Add Tenant</h3>
            <button type="button" class="dialog-close" onclick="closeAddModal()">&times;</button>
        </div>
        <form action="{{ route('tenants.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="add-name">Tenant Name</label>
                <input type="text" name="name" id="add-name" class="form-control" required value="{{ old('name') }}" placeholder="e.g. John Doe">
            </div>
            <div class="form-group">
                <label for="add-phone">Phone Number (WhatsApp)</label>
                <input type="text" name="phone_number" id="add-phone" class="form-control" required value="{{ old('phone_number') }}" placeholder="e.g. 08123456789">
            </div>
            <div class="form-group">
                <label for="add-room">Room Number</label>
                <input type="text" name="room_number" id="add-room" class="form-control" required value="{{ old('room_number') }}" placeholder="e.g. A101">
            </div>
            <div class="form-group">
                <label for="add-rent">Monthly Rent Rate (IDR)</label>
                <input type="number" name="monthly_rent" id="add-rent" class="form-control" required value="{{ old('monthly_rent') }}" placeholder="e.g. 1500000">
            </div>
            <div class="form-group">
                <label for="add-due">Due Day (Day of Month)</label>
                <input type="number" name="due_day" id="add-due" class="form-control" required min="1" max="31" value="{{ old('due_day', 10) }}" placeholder="e.g. 10">
                <span class="form-help">Enter a date between 1 and 31.</span>
            </div>
            <div class="form-group">
                <label for="add-move-in">Move In Date</label>
                <input type="date" name="move_in_date" id="add-move-in" class="form-control" required value="{{ old('move_in_date', now()->format('Y-m-d')) }}">
            </div>
            <div class="form-group">
                <label for="add-months">Lease Duration & Initial Payment</label>
                <select name="months" id="add-months" class="form-control" required>
                    <option value="1" {{ old('months') == 1 ? 'selected' : '' }}>1 Month</option>
                    <option value="3" {{ old('months', 3) == 3 ? 'selected' : '' }}>3 Months</option>
                    <option value="6" {{ old('months') == 6 ? 'selected' : '' }}>6 Months</option>
                    <option value="12" {{ old('months') == 12 ? 'selected' : '' }}>12 Months</option>
                </select>
            </div>
            <div class="dialog-footer">
                <button type="button" class="btn" onclick="closeAddModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Tenant</button>
            </div>
        </form>
    </dialog>

    <!-- Dialog Edit Tenant -->
    <dialog id="edit-tenant-modal">
        <div class="dialog-header">
            <h3 class="dialog-title">Edit Tenant</h3>
            <button type="button" class="dialog-close" onclick="closeEditModal()">&times;</button>
        </div>
        <form id="edit-form" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="tenant_id" id="edit-tenant-id">
            <div class="form-group">
                <label for="edit-name">Tenant Name</label>
                <input type="text" name="name" id="edit-name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="edit-phone">Phone Number (WhatsApp)</label>
                <input type="text" name="phone_number" id="edit-phone" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="edit-room">Room Number</label>
                <input type="text" name="room_number" id="edit-room" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="edit-rent">Monthly Rent Rate (IDR)</label>
                <input type="number" name="monthly_rent" id="edit-rent" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="edit-due">Due Day (Day of Month)</label>
                <input type="number" name="due_day" id="edit-due" class="form-control" required min="1" max="31">
                <span class="form-help">Enter a date between 1 and 31.</span>
            </div>
            <div class="form-group">
                <label for="edit-move-in">Move In Date</label>
                <input type="date" name="move_in_date" id="edit-move-in" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="edit-move-out">Move Out Date</label>
                <input type="date" name="move_out_date" id="edit-move-out" class="form-control" required>
            </div>
            <div class="dialog-footer">
                <button type="button" class="btn" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </dialog>

    <!-- Dialog Payment History -->
    <dialog id="history-modal">
        <div class="dialog-header">
            <h3 class="dialog-title">Payment History - <span id="history-tenant-name"></span></h3>
            <button type="button" class="dialog-close" onclick="closeHistoryModal()">&times;</button>
        </div>
        
        <!-- Record Payment Form for Current Month -->
        <div id="history-pay-form-wrapper" style="margin-bottom: var(--spacing-lg); padding-bottom: var(--spacing-lg); border-bottom: 1px solid var(--color-border);">
            <form id="history-pay-form" method="POST" style="display: none;">
                @csrf
                <div style="display: flex; flex-direction: column; gap: var(--spacing-md);">
                    <div>
                        <strong style="display: block; font-size: 0.875rem;">Record Rent Payment</strong>
                        <span class="form-help">Select the extension duration below to log this payment.</span>
                    </div>
                    <div style="display: flex; align-items: flex-end; gap: var(--spacing-md);">
                        <div class="form-group" style="margin-bottom: 0; flex-grow: 1;">
                            <label for="pay-months" style="font-size: 0.75rem; font-weight: 600;">Duration</label>
                            <select name="months" id="pay-months" class="form-control" required style="padding: 0.375rem var(--spacing-sm); font-size: 0.875rem; height: 38px;">
                                <option value="1">1 Month</option>
                                <option value="3">3 Months</option>
                                <option value="6">6 Months</option>
                                <option value="12">12 Months</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm" style="height: 38px;">✓ Mark as Paid</button>
                    </div>
                </div>
            </form>
        </div>

        <ul class="history-list" id="history-list-container">
            <!-- Dynamically populated -->
        </ul>

        <div class="dialog-footer">
            <button type="button" class="btn" onclick="closeHistoryModal()">Close</button>
        </div>
    </dialog>

    <script>
        function openAddModal() {
            document.getElementById('add-tenant-modal').showModal();
        }

        function closeAddModal() {
            document.getElementById('add-tenant-modal').close();
        }

        function openEditModal(button) {
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const phone = button.getAttribute('data-phone');
            const room = button.getAttribute('data-room');
            const rent = button.getAttribute('data-rent');
            const due = button.getAttribute('data-due');
            const moveIn = button.getAttribute('data-move-in');
            const moveOut = button.getAttribute('data-move-out');

            const form = document.getElementById('edit-form');
            form.action = `/tenants/${id}`;

            document.getElementById('edit-tenant-id').value = id;
            document.getElementById('edit-name').value = name;
            document.getElementById('edit-phone').value = phone;
            document.getElementById('edit-room').value = room;
            document.getElementById('edit-rent').value = rent;
            document.getElementById('edit-due').value = due;
            document.getElementById('edit-move-in').value = moveIn || '';
            document.getElementById('edit-move-out').value = moveOut || '';

            document.getElementById('edit-tenant-modal').showModal();
        }

        function closeEditModal() {
            document.getElementById('edit-tenant-modal').close();
        }

        function openHistoryModal(button) {
            const name = button.getAttribute('data-name');
            const id = button.getAttribute('data-id');
            const status = button.getAttribute('data-status');
            const history = JSON.parse(button.getAttribute('data-history') || '[]');
            
            document.getElementById('history-tenant-name').textContent = name;
            
            const payForm = document.getElementById('history-pay-form');
            if (status !== 'paid') {
                payForm.action = `/tenants/${id}/pay`;
                payForm.style.display = 'block';
            } else {
                payForm.style.display = 'none';
            }
            
            const container = document.getElementById('history-list-container');
            container.innerHTML = '';
            
            if (history.length === 0) {
                container.innerHTML = `<li class="history-item">
                    <div>
                        <div class="history-month">No payment history found</div>
                        <div class="history-meta">Mark rent as paid to log transactions.</div>
                    </div>
                </li>`;
            } else {
                history.forEach(tx => {
                    const billingDate = new Date(tx.billing_month);
                    const billingMonthStr = billingDate.toLocaleDateString('en-US', { year: 'numeric', month: 'long' });
                    
                    let paidText = 'Unpaid';
                    let paidMeta = '';
                    if (tx.paid_at) {
                        const paidDate = new Date(tx.paid_at);
                        paidText = 'Paid';
                        paidMeta = 'Paid on ' + paidDate.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
                    }
                    
                    const rentFormatted = 'Rp' + Number(tx.amount).toLocaleString('id-ID');
                    
                    const li = document.createElement('li');
                    li.className = 'history-item';
                    li.innerHTML = `
                        <div>
                            <div class="history-month">${billingMonthStr}</div>
                            <div class="history-meta">${paidMeta || 'Due date: ' + new Date(tx.due_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-weight: 700; margin-bottom: 4px;">${rentFormatted}</div>
                            <span class="badge ${tx.paid_at ? 'badge-paid' : 'badge-unpaid'}">${tx.paid_at ? '✓ ' : '○ '} ${paidText}</span>
                        </div>
                    `;
                    container.appendChild(li);
                });
            }
            
            document.getElementById('history-modal').showModal();
        }

        function closeHistoryModal() {
            document.getElementById('history-modal').close();
        }
        
        // Auto open Add Modal if validation errors exist and we are adding
        @if ($errors->any() && !old('_method'))
            window.addEventListener('DOMContentLoaded', () => {
                openAddModal();
            });
        @endif

        // Auto open Edit Modal if validation errors exist and we are updating
        @if ($errors->any() && old('_method') === 'PUT')
            window.addEventListener('DOMContentLoaded', () => {
                const id = "{{ old('tenant_id') }}";
                const form = document.getElementById('edit-form');
                form.action = `/tenants/${id}`;

                document.getElementById('edit-tenant-id').value = id;
                document.getElementById('edit-name').value = "{{ old('name') }}";
                document.getElementById('edit-phone').value = "{{ old('phone_number') }}";
                document.getElementById('edit-room').value = "{{ old('room_number') }}";
                document.getElementById('edit-rent').value = "{{ old('monthly_rent') }}";
                document.getElementById('edit-due').value = "{{ old('due_day') }}";
                document.getElementById('edit-move-in').value = "{{ old('move_in_date') }}";
                document.getElementById('edit-move-out').value = "{{ old('move_out_date') }}";

                document.getElementById('edit-tenant-modal').showModal();
            });
        @endif
    </script>
@endsection