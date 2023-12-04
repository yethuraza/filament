<?php

namespace Database\Factories;

use App\Enums\Region;
use App\Models\Venue;
use App\Models\Conference;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConferenceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Conference::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $startday = now()->addMonths(9);
        $endday = now()->addMonths(10)->addDays(5);
        return [
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'start_data' => $startday,
            'end_data' => $endday,
            'status' => $this->faker->randomElement([
                'draft',
                'published',
                'archived'
            ]),
            'region' => $this->faker->randomElement(Region::class),
            'venue_id' => null,
        ];
    }
}
