<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pedido>
 */
class PedidoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'partida' => $this->faker->address,
            'destino' => $this->faker->address,
            'm_pago' => $this->faker->randomElement(['transferencia', 'efectivo']),
            'observacionCli' => $this->faker->sentence,
            'observacionAdmin' => $this->faker->sentence,
            'comentarioAdmin' => $this->faker->sentence,
            'comentarioCli' => $this->faker->sentence,
            'fecha_hora' => $this->faker->dateTimeBetween('now', '+1 week'),
            'estado' => $this->faker->randomElement(['pendiente', 'finalizado']),
            // Agrega aquí los demás atributos del modelo Pedido
        ];
    }
}
