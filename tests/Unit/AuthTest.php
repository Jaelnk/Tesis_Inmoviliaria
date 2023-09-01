<?php

namespace Tests\Unit;

//use PHPUnit\Framework\TestCase;
use Tests\TestCase;

use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Categoria;
use App\Models\Servicio;
use App\Models\Pedido;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Unit\factory;

class AuthTest extends TestCase
{

/*     use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        // Ejecutar los seeders aquí
        $this->seed();
    } */



    public function testRegister()
    {
        // Crear un objeto Request simulado con los datos de entrada
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Secret123',
            'cedula' => '1234567890',
            'celular' => '1234567890',
            'fnac' => '1990-01-01',
            'direccion' => '123 Street',
        ];
        $response = $this->post('/register', $userData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'metodo register exitosa',
                'user' => [
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                ],
            ]);
    }

    public function testLogin()
    {
        $userData = [
            'email' => 'adminEmail@example.com',
            'password' => 'administrador',
        ];

        $response = $this->postJson('/login', $userData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Inicio de sesión exitoso',
                'user' => [
                    'email' => $userData['email'],
                ],
            ]);
    }


    public function testPerfil()
    {
        $user = User::create([
            'name' => 'John',
            'email' => 'john2@example.com',
            'password' => bcrypt('Secret123'),
            'cedula' => '1234567890',
            'celular' => '1234567890',
            'fnac' => '1990-01-01',
            'direccion' => '123 Street',
            'role_id' => '2'
        ]);
        $this->actingAs($user);

        // Simular una solicitud PUT a '/perfil'
        $response = $this->put('/perfil', [
            'name' => 'UpdatedName',
        ]);
        $response->assertStatus(200);
    }



    public function testLogout()
    {
        // Crear un usuario de prueba manualmente
        $user = User::create([
            'name' => 'Pedro',
            'email' => 'john3@example.com',
            'password' => bcrypt('Secret123'),
            'cedula' => '1234567890',
            'celular' => '1234567890',
            'fnac' => '1990-01-01',
            'direccion' => '123 Street',
            'role_id' => '2',
        ]);
        $token = $user->createToken('authToken')->plainTextToken;
        $this->withCookie('cookie_token', $token);
        $this->actingAs($user);

        // Hacer una solicitud POST a '/logout'
        $response = $this->postJson('/logout');
        $response->assertStatus(200);
    }


    public function testUpdatePassword()
    {
        // Crear un usuario de prueba manualmente
        $user = User::create([
            'name' => 'John',
            'email' => 'john4@example.com',
            'password' => bcrypt('OldPassword123'),
            'cedula' => '1234567890',
            'celular' => '1234567890',
            'fnac' => '1990-01-01',
            'direccion' => '123 Street',
            'role_id' => '2',
        ]);
        $updatedData = [
            'password_actual' => 'OldPassword123',
            'password' => 'NewPassword123',
        ];
        // Simular la autenticación del usuario
        $this->actingAs($user);
        // Simular una solicitud PUT a '/updatePassword'
        $response = $this->putJson('/updatePassword', $updatedData);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Contraseña actualizada',
            ]);
        // Asegurarse de que la contraseña haya sido actualizada en la base de datos
        $this->assertTrue(Hash::check($updatedData['password'], $user->fresh()->password));
    }

    public function testRegisterEmployee()
    {
        // Creamos un usuario con el rol de administrador
        $userData = [
            'email' => 'adminEmail@example.com',
            'password' => 'administrador',
        ];
        $response = $this->postJson('/login', $userData);
        // Verificamos que el inicio de sesión sea exitoso
        $response->assertStatus(200);
        // Obtenemos el usuario autenticado
        $user = auth()->user();
        // Simulamos la autenticación del usuario
        $this->actingAs($user);
        // Datos para el nuevo empleado
        $userData = [
            'name' => 'John Doe',
            'email' => 'johndoe6@example.com',
            'password' => 'password123',
            'cedula' => '1234567891',
            'celular' => '9876543210',
            'fnac' => '1990-01-01',
            'direccion' => 'Calle Principal 123',
        ];
        // Realizamos la solicitud al método registerEmployee
        $response = $this->postJson('/admin/registerEmployee', $userData);
        // Verificamos que la respuesta sea exitosa y los datos sean correctos
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'metodo registerEmployee exitoso',
            ]);
    }

    public function testIndexCat()
    {
        // Creamos un usuario con el rol de administrador
        $userData = [
            'email' => 'adminEmail@example.com',
            'password' => 'administrador',
        ];
        $response = $this->postJson('/login', $userData);
        // Verificamos que el inicio de sesión sea exitoso
        $response->assertStatus(200);
        // Obtenemos el usuario autenticado
        $user = auth()->user();
        // Simulamos la autenticación del usuario
        $this->actingAs($user);
        // Realizamos la solicitud al método indexCat
        $response = $this->getJson('/admin/categories');
        // Verificamos que la respuesta sea exitosa y contenga los datos correctos
        $response->assertStatus(200)
            ->assertJson(Categoria::all()->toArray());
    }

    public function testStoreCat()
    {
        // Creamos un usuario con el rol de administrador
        $userData = [
            'email' => 'adminEmail@example.com',
            'password' => 'administrador',
        ];
        $response = $this->postJson('/login', $userData);
        // Verificamos que el inicio de sesión sea exitoso
        $response->assertStatus(200);
        // Obtenemos el usuario autenticado
        $user = auth()->user();
        // Simulamos la autenticación del usuario
        $this->actingAs($user);
        // Datos para la nueva categoría
        $categoryData = [
            'nombre' => 'Nueva Categoría',
            'descripcion' => 'Descripción de la nueva categoría',
        ];
        // Adjuntamos el archivo de imagen
        $categoryData['image_url'] = UploadedFile::fake()->image('category.jpg');
        // Realizamos la solicitud al método storeCat
        $response = $this->postJson('/admin/categories', $categoryData);
        // Verificamos que la respuesta sea exitosa y los datos sean correctos
        $response->assertStatus(201);
    }

    public function testUpdateCat()
    {
        // Creamos un usuario con el rol de administrador
        $userData = [
            'email' => 'adminEmail@example.com',
            'password' => 'administrador',
        ];
        $response = $this->postJson('/login', $userData);
        // Verificamos que el inicio de sesión sea exitoso
        $response->assertStatus(200);
        // Obtenemos el usuario autenticado
        $user = auth()->user();
        // Simulamos la autenticación del usuario
        $this->actingAs($user);
        // Datos para la nueva categoría
        $categoryData = [
            'nombre' => 'Nueva Categoría2',
            'descripcion' => 'Descripción de la nueva categoría',
        ];
        // Adjuntamos el archivo de imagen
        $categoryData['image_url'] = UploadedFile::fake()->image('category.jpg');
        // Realizamos la solicitud al método storeCat
        $response = $this->postJson('/admin/categories', $categoryData);
        // Obtenemos el ID de la categoría creada
        $categoryId = $response->json('id');
        // Datos para la actualización de la categoría
        $updatedCategoryData = [
            'nombre' => 'Actualizada',
            'descripcion' => 'actualizada',
        ];
        // Realizamos la solicitud al método updateCat
        $response = $this->putJson('/admin/categories/'.$categoryId, $updatedCategoryData);
        // Verificamos que la respuesta sea exitosa y los datos sean correctos
        $response->assertStatus(200);
    }


    public function testDestroyCat()
    {
        // Creamos un usuario con el rol de administrador
        $userData = [
            'email' => 'adminEmail@example.com',
            'password' => 'administrador',
        ];
        $response = $this->postJson('/login', $userData);
        $response->assertStatus(200);
        $user = auth()->user();
        $this->actingAs($user);
        $categoryData = [
            'nombre' => 'Categoría33',
            'descripcion' => 'Descripción',
        ];
        $categoryData['image_url'] = UploadedFile::fake()->image('category.jpg');
        $response = $this->postJson('/admin/categories', $categoryData);
        $categoryId = $response->json('id');
        $response = $this->deleteJson('/admin/categories/'.$categoryId);

        $response->assertStatus(200);
    }


    //Servicios Tests

    //mostrar servicios de una cat
    public function testShowCat()
    {
        // Creamos un usuario con el rol de administrador
        $userData = [
            'email' => 'adminEmail@example.com',
            'password' => 'administrador',
        ];
        $response = $this->postJson('/login', $userData);
        // Verificamos que el inicio de sesión sea exitoso
        $response->assertStatus(200);
        // Obtenemos el usuario autenticado
        $user = auth()->user();
        // Simulamos la autenticación del usuario
        $this->actingAs($user);
        // Creamos una categoría
        $categoria = Categoria::create([
            'nombre' => 'Mudanza Completa',
            'descripcion' => 'Incluye diferentes servicios de embalaje, Camión/Transporte y de Personal',
        ]);
        $response = $this->actingAs($user)->get("/admin/categories/{$categoria->id}/services");
        $response->assertStatus(200);
    }




    public function testStoreServ()
    {
        $userData = [
            'email' => 'adminEmail@example.com',
            'password' => 'administrador',
        ];
        $response = $this->postJson('/login', $userData);
        $response->assertStatus(200);
        $user = auth()->user();
        $this->actingAs($user);
        $categoria = Categoria::create([
            'nombre' => 'Mudanza Completa',
            'descripcion' => 'Incluye diferentes servicios de embalaje, Camión/Transporte y de Personal',
        ]);
        $servicioData = [
            'nombre' => 'Nombre del servicio',
            'vehiculo' => 'Tipo de vehículo',
            'descripcion' => 'Descripción del servicio',
            'precio_h' => 10.99,
            'image' => UploadedFile::fake()->image('imagen.jpg'),
            'categorias' => [$categoria->id],
        ];
        $response = $this->actingAs($user)->postJson("/admin/categories/{$categoria->id}/newServ", $servicioData);
        $response->assertStatus(201);
    }



    public function testShowServ()
    {
        // Creamos un usuario con el rol de administrador
        $userData = [
            'email' => 'adminEmail@example.com',
            'password' => 'administrador',
        ];
        $response = $this->postJson('/login', $userData);
        // Verificamos que el inicio de sesión sea exitoso
        $response->assertStatus(200);
        // Obtenemos el usuario autenticado
        $user = auth()->user();
        // Simulamos la autenticación del usuario
        $this->actingAs($user);
        // Creamos un servicio manualmente
        $servicioData = [
            'nombre' => 'Transporte liviano',
            'vehiculo' => 'Toyota Hiace',
            'descripcion' => 'Furgoneta de tamaño mediano con capacidad de 10 m³',
            'precio_h' => 24.99,
        ];
        $servicio = Servicio::create($servicioData);
        $response = $this->actingAs($user)->get("/admin/service/{$servicio->id}");
        $response->assertStatus(200)
            ->assertJson($servicio->toArray());
    }


    public function testUpdateServ()
    {
        // Creamos un usuario con el rol de administrador
        $userData = [
            'email' => 'adminEmail@example.com',
            'password' => 'administrador',
        ];
        $response = $this->postJson('/login', $userData);
        // Verificamos que el inicio de sesión sea exitoso
        $response->assertStatus(200);
        // Obtenemos el usuario autenticado
        $user = auth()->user();
        // Simulamos la autenticación del usuario
        $this->actingAs($user);
        $servicioData = [
            'nombre' => 'Transporte liviano',
            'vehiculo' => 'Toyota Hiace',
            'descripcion' => 'Furgoneta de tamaño mediano con capacidad de 10 m³',
            'precio_h' => 24.99,
        ];
        $servicio = Servicio::create($servicioData);
        $updatedData = [
            'nombre' => 'Servicio Actualizado',
            'descripcion' => 'Descripción actualizada',
        ];
        $response = $this->actingAs($user)->putJson("/admin/service/{$servicio->id}", $updatedData);
        $response->assertStatus(200)
            ->assertJson($updatedData);
        $this->assertDatabaseHas('servicios', [
            'id' => $servicio->id,
            'nombre' => $updatedData['nombre'],
            'descripcion' => $updatedData['descripcion'],
        ]);
    }

    public function testDestroyServ()
    {
        // Creamos un usuario con el rol de administrador
        $userData = [
            'email' => 'adminEmail@example.com',
            'password' => 'administrador',
        ];
        $response = $this->postJson('/login', $userData);
        // Verificamos que el inicio de sesión sea exitoso
        $response->assertStatus(200);
        // Obtenemos el usuario autenticado
        $user = auth()->user();
        // Simulamos la autenticación del usuario
        $this->actingAs($user);
        $servicioData = [
            'nombre' => 'Nombre del servicio',
            'vehiculo' => 'Tipo de vehículo',
            'descripcion' => 'Descripción del servicio',
            'precio_h' => 10.99,
        ];
        $servicio = Servicio::create($servicioData);
        $response = $this->actingAs($user)->delete("/admin/service/{$servicio->id}");
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Servicio eliminado exitosamente'
            ]);
    }

    //PEDIDOZZ


    public function testShowPedidos()
    {
        $userData = [
            'email' => 'adminEmail@example.com',
            'password' => 'administrador',
        ];
        $response = $this->postJson('/login', $userData);
        $response->assertStatus(200);
        $user = auth()->user();
        $this->actingAs($user);
        $response = $this->actingAs($user)->get('/pedidos');
        $response->assertStatus(200);
    }






    //CLIENTE

    public function testIndexCatCliente()
    {
        // Creamos un usuario con el rol de administrador
        $userData = [
            'email' => 'john@example.com',
            'password' => 'Secret123',
        ];
        $response = $this->postJson('/login', $userData);
        // Verificamos que el inicio de sesión sea exitoso
        $response->assertStatus(200);
        // Obtenemos el usuario autenticado
        $user = auth()->user();
        // Simulamos la autenticación del usuario
        $this->actingAs($user);
        // Realizamos la solicitud al método indexCat
        $response = $this->getJson('cli/categories');
        // Verificamos que la respuesta sea exitosa y contenga los datos correctos
        $response->assertStatus(200)
            ->assertJson(Categoria::all()->toArray());
    }

    //mostrar servicios de una cat
    public function testShowCatCliente()
    {
        // Creamos un usuario con el rol de cliente
        $userData = [
            'email' => 'john@example.com',
            'password' => 'Secret123',
        ];
        $response = $this->postJson('/login', $userData);
        // Verificamos que el inicio de sesión sea exitoso
        $response->assertStatus(200);
        // Obtenemos el usuario autenticado
        $user = auth()->user();
        // Simulamos la autenticación del usuario
        $this->actingAs($user);
        // Creamos una categoría
        $categoria = Categoria::create([
            'nombre' => 'Mudanza Completa',
            'descripcion' => 'Incluye diferentes servicios de embalaje, Camión/Transporte y de Personal',
        ]);
        $response = $this->actingAs($user)->get("/cli/categories/{$categoria->id}/services");
        $response->assertStatus(200);
    }


    //crear pedido

    public function testNewPedido()
    {
        // Creamos un usuario con el rol de cliente
        $userData = [
            'email' => 'john@example.com',
            'password' => 'Secret123',
        ];
        $response = $this->postJson('/login', $userData);
        // Verificamos que el inicio de sesión sea exitoso
        $response->assertStatus(200);
        // Obtenemos el usuario autenticado
        $user = auth()->user();
        // Simulamos la autenticación del usuario
        $this->actingAs($user);
        // Datos para la solicitud POST
        $requestData = [
            'partida' => 'Ubicación de partida',
            'destino' => 'Ubicación de destino',
            'm_pago' => 'transferencia',
            'servicios' => [1, 2, 3], // IDs de servicios existentes
            'observaciones' => 'Observaciones del cliente',
            'fecha_hora' => now()->addDay()->format('Y-m-d H:i:s'), // Fecha y hora futura
        ];
        // Enviamos la solicitud POST a la ruta /newPedido
        $response = $this->actingAs($user)->postJson('/newPedido', $requestData);
        $response->assertStatus(200);

    }


    public function testShowPedido()
    {
        // Creamos un usuario con el rol de cliente
        $userData = [
            'email' => 'john@example.com',
            'password' => 'Secret123',
        ];
        $response = $this->postJson('/login', $userData);
        // Verificamos que el inicio de sesión sea exitoso
        $response->assertStatus(200);
        // Obtenemos el usuario autenticado
        $user = auth()->user();
        // Simulamos la autenticación del usuario
        $this->actingAs($user);
        // Enviamos la solicitud GET a la ruta /cli/pedidos
        $response = $this->actingAs($user)->get('/cli/pedidos');
        $response->assertStatus(200);
    }




    //admin- Nota: primero ejecutar test de creación de pedido
    public function testPutState()
    {
        // Creamos un usuario con el rol de cliente
        $userData = [
            'email' => 'adminEmail@example.com',
            'password' => 'administrador',
        ];
        $response = $this->postJson('/login', $userData);
        // Verificamos que el inicio de sesión sea exitoso
        $response->assertStatus(200);
        // Obtenemos el usuario autenticado
        $user = auth()->user();
        // Simulamos la autenticación del usuario
        $this->actingAs($user);
        $requestData = [
            'estado' => 'aprobado',
            'observacionAdmin' => 'pedido asignado',
            'empleado_id' => 6 // ID de un empleado existente
        ];
        $response = $this->actingAs($user)->putJson("/pedidos/1", $requestData);
        $response->assertStatus(200);
    }

    public function testPedidosEmp()
    {
        // Creamos un usuario con el rol de empleado
        $userData = [
            'email' => 'johndoe6@example.com',
            'password' => 'password123',
        ];
        $response = $this->postJson('/login', $userData);
        // Verificamos que el inicio de sesión sea exitoso
        $response->assertStatus(200);
        // Obtenemos el usuario autenticado
        $user = auth()->user();
        // Simulamos la autenticación del usuario
        $this->actingAs($user);
        $response = $this->actingAs($user)->get("/emp/pedidos");
        $response->assertStatus(200);
    }



    //empleado- Nota: primero ejecutar test de creación de pedido
    public function testPutStateEmp()
    {
        // Creamos un usuario con el rol de cliente
        $userData = [
            'email' => 'johndoe6@example.com',
            'password' => 'password123',
        ];
        $response = $this->postJson('/login', $userData);
        // Verificamos que el inicio de sesión sea exitoso
        $response->assertStatus(200);
        // Obtenemos el usuario autenticado
        $user = auth()->user();
        // Simulamos la autenticación del usuario
        $this->actingAs($user);
        $requestData = [
            'estado' => 'finalizado',
        ];
        $response = $this->actingAs($user)->putJson("/emp/pedido/1", $requestData);
        $response->assertStatus(200);
    }





//PRIMERO FINALIZAR PEDIDO
    public function testComentarPedido()
    {
        // Creamos un usuario con el rol de cliente
        $userData = [
            'email' => 'john@example.com',
            'password' => 'Secret123',
        ];
        $response = $this->postJson('/login', $userData);
        // Verificamos que el inicio de sesión sea exitoso
        $response->assertStatus(200);
        // Obtenemos el usuario autenticado
        $user = auth()->user();
        // Simulamos la autenticación del usuario
        $this->actingAs($user);
        // Datos para la solicitud PUT
        $requestData = [
            'comentarioCli' => 'Comentario del cliente',
            'calificacion' => 4
        ];
        // Enviamos la solicitud PUT a la ruta /cli/pedido/{id}
        $response = $this->actingAs($user)->putJson('/cli/pedido/1', $requestData);
        $response->assertStatus(200);
    }


    public function testComentarPedidoAdmin()
    {
        // Creamos un usuario con el rol de admin
        $userData = [
            'email' => 'adminEmail@example.com',
            'password' => 'administrador',
        ];
        $response = $this->postJson('/login', $userData);
        // Verificamos que el inicio de sesión sea exitoso
        $response->assertStatus(200);
        // Obtenemos el usuario autenticado
        $user = auth()->user();
        // Simulamos la autenticación del usuario
        $this->actingAs($user);
        // Datos para la solicitud PUT
        $requestData = [
            "comentarioAdmin" => "Ya nos comunicamos"
        ];
        // Enviamos la solicitud PUT a la ruta /cli/pedido/{id}
        $response = $this->actingAs($user)->putJson('pedido/1', $requestData);
        $response->assertStatus(200);
    }





}
