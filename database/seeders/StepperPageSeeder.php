<?php

namespace Database\Seeders;

use App\Models\StepperPage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StepperPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stepperPages = [
            [
                'title' => 'Drive with Purpose',
                'description' => 'Join our team to help patients get home safely and on time. Make a difference in someone’s day—one trip at a time. ',
                'image' => 'uploads/stepper/1.jpg',
                'order_no' => 1,
                'status' => 'active',
            ],
            [
                'title' => 'Pick Up. Drop Off. Get Paid.',
                'description' => 'View available trips, accept the ones that fit your schedule, and get paid for every completed ride. Its that simple.',
                'image' => 'uploads/stepper/2.jpg', 
                'order_no' => 2,
                'status' => 'active',
            ],
            [
                'title' => 'Work on Your Terms',
                'description' => 'Set your availability, manage your trips, and track your earnings—all from the app.',
                'image' => 'uploads/stepper/3.jpg',
                'order_no' => 3,
                'status' => 'active',
            ],
        ];

        foreach ($stepperPages as $page) {
            StepperPage::create($page);
        }
    }
}
