<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile JKN Service</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f3f4f6;
        }
        
        .nav-item {
            @apply px-4 py-2 text-gray-700 hover:bg-blue-500 hover:text-white rounded-md transition-colors;
        }
        
        .nav-item.active {
            @apply bg-blue-600 text-white;
        }
    </style>
</head>
<body>
    <nav class="bg-white shadow-md">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="text-xl font-bold text-blue-600">Mobile JKN Service</div>
                <div class="hidden md:flex space-x-2">
                    <a href="{{ route('taskid.logs') }}" class="nav-item {{ request()->routeIs('taskid.logs') ? 'active' : '' }}">
                        Task ID Logs
                    </a>
                    <a href="{{ route('command.index') }}" class="nav-item {{ request()->routeIs('command.index') ? 'active' : '' }}">
                        Run Command
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <footer class="bg-white py-4 mt-8 border-t">
        <div class="container mx-auto px-4 text-center text-gray-500 text-sm">
            &copy; {{ date('Y') }} Mobile JKN Integration Service
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    @stack('scripts')
</body>
</html>
