@extends('layouts.app')

@section('content')
    <h1 class="text-2xl font-bold text-center mt-6 mb-6">Usuarios - DataTables</h1>
    <div class="flex justify-end">
        <a href="{{ route('users.create') }}" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Nuevo Usuario</a>
    </div>
    <div class="container mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-8">
        <div class="bg-white shadow-md rounded-lg p-6">
            <table id="users-table" class="w-full text-left border-collapse rounded-lg shadow-lg">
                <thead class="bg-gray-900 text-white">
                    <tr>
                        <th class="px-6 py-3 border-b border-gray-700 text-sm font-medium uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 border-b border-gray-700 text-sm font-medium uppercase tracking-wider">Nombre</th>
                        <th class="px-6 py-3 border-b border-gray-700 text-sm font-medium uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 border-b border-gray-700 text-sm font-medium uppercase tracking-wider">Rol</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
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
                ajax: @json(route('users.data')),
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    { data: 'role', name: 'role' },
                    { 
                        data: 'id',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                <div class="flex space-x-2">
                                    <a href="/usuarios/${data}/edit" class="bg-yellow-500 text-black px-3 py-1 rounded-md shadow hover:bg-yellow-600 transition duration-200">
                                        Editar
                                    </a>
                                    <form action="/usuarios/${data}" method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar este usuario?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded-md shadow hover:bg-red-600 transition duration-200">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            `;
                        }
                    }
                ],
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
                }
            });
        });
    </script>
@endsection
