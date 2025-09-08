<?php
// app/Http/Controllers/ClienteController.php
namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClienteController extends Controller
{
    public function index()
    {
        // opcional: un listado simple
        $clientes = Cliente::with('usuario:id,name')
            ->latest()->paginate(10)->withQueryString();

        return Inertia::render('Clientes/Index', [
            'clientes' => $clientes,
        ]);
    }

    public function create()
    {
        return Inertia::render('Clientes/Create', [
            'usuarios' => User::orderBy('name')->get(['id','name']),
            'defaults' => [
                'estatus'    => 'activo',
                'usuario_id' => auth()->id(),   // autoseleccionar el usuario actual
            ],
        ]);
    }

    public function store(Request $request)
    {
      $data = $request->validate([
        'nombre'  => ['required','string','max:255'],
        'estatus' => ['required','in:activo,inactivo'],
    ]);

    // aquí se asigna automáticamente el usuario de la sesión
    $data['usuario_id'] = auth()->id();

        Cliente::create($data);

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente creado correctamente.');
    }
}
