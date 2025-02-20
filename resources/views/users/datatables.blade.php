@extends('layouts.app')

@section('content')
    <h1 class="text-2xl font-bold text-center mt-6 mb-6">Usuarios - DataTables</h1>

    <div class="container mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-8">
        <div class="bg-white shadow-md rounded-lg p-6">
            <table id="users-table" class="w-full border-collapse rounded-lg shadow text-center">
                <thead class="bg-gray-900 text-white">
                    <tr>
                        <th class="py-4 px-6 border-b border-gray-700">ID</th>
                        <th class="py-4 px-6 border-b border-gray-700">Nombre</th>
                        <th class="py-4 px-6 border-b border-gray-700">Email</th>
                        <th class="py-4 px-6 border-b border-gray-700">Rol</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Incluir DataTables y jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function () {
            console.log("DataTables ejecutándose...");

            $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('users.data') }}",
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    { data: 'role', name: 'role' }
                ],
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
                }
            });
        });
    </script>
@endsection