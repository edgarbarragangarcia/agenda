<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --primary-color: #2563eb;
        --secondary-color: #3b82f6;
        --success-color: #059669;
        --danger-color: #dc2626;
        --warning-color: #d97706;
        --info-color: #0891b2;
    }
    
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f8fafc;
        color: #1e293b;
    }

    .navbar {
        background-color: white;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    }

    .navbar .nav-link {
        color: #1e293b;
        font-weight: 500;
        padding: 0.5rem 1rem;
        transition: color 0.2s ease-in-out;
    }

    .navbar .nav-link:hover {
        color: var(--primary-color);
    }

    .navbar .nav-link.active {
        color: var(--primary-color);
    }

    .navbar-brand {
        font-weight: 600;
        color: var(--primary-color) !important;
    }

    .dropdown-item {
        padding: 0.5rem 1rem;
        font-weight: 500;
    }

    .dropdown-item:hover {
        background-color: #f8fafc;
    }

    .dropdown-item.text-danger:hover {
        background-color: #fef2f2;
    }

    .page-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
        border-radius: 0 0 1rem 1rem;
    }

    .btn {
        border-radius: 0.5rem;
        padding: 0.5rem 1rem;
        font-weight: 500;
        transition: all 0.2s ease-in-out;
    }

    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .btn-primary:hover {
        background-color: var(--secondary-color);
        border-color: var(--secondary-color);
        transform: translateY(-1px);
    }

    .card {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s ease-in-out;
    }

    .form-control, .form-select {
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        border: 1px solid #e5e7eb;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .alert {
        border-radius: 0.5rem;
        border: none;
    }
</style>
