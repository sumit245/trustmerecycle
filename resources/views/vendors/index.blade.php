@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-900">Vendor Management</h2>
        <div class="flex space-x-3">
            <a href="{{ route('vendors.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Add Vendor
            </a>
            <button onclick="document.getElementById('importForm').classList.toggle('hidden')" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                Import Vendors
            </button>
        </div>
    </div>

    <!-- Import Form -->
    <div id="importForm" class="hidden bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-4">Import Vendors from Excel</h3>
        <form action="{{ route('vendors.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label for="file" class="block text-sm font-medium text-gray-700">Select Excel File</label>
                <input type="file" name="file" id="file" accept=".xlsx,.xls" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                <p class="mt-1 text-sm text-gray-500">Supported formats: .xlsx, .xls (Max: 10MB)</p>
            </div>
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                Import
            </button>
        </form>
    </div>

    <!-- Vendors Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table id="vendors-table" class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Godown Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            </tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function() {
    var table = $('#vendors-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('vendors.index') }}",
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'phone', name: 'phone' },
            { data: 'godown_name', name: 'godown_name', orderable: false },
            { data: 'location', name: 'location', orderable: false },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ vendors per page",
            info: "Showing _START_ to _END_ of _TOTAL_ vendors",
            infoEmpty: "Showing 0 to 0 of 0 vendors",
            infoFiltered: "(filtered from _MAX_ total vendors)"
        }
    });

    // Delete vendor
    $(document).on('click', '.delete-vendor', function() {
        var vendorId = $(this).data('id');
        if (confirm('Are you sure you want to delete this vendor? This will also delete associated godowns.')) {
            $.ajax({
                url: '/vendors/' + vendorId,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        table.ajax.reload();
                        alert('Vendor deleted successfully!');
                    }
                },
                error: function(xhr) {
                    alert('Error deleting vendor: ' + (xhr.responseJSON?.message || 'Unknown error'));
                }
            });
        }
    });
});
</script>
@endsection

