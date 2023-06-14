<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


use App\Models\Course;
use App\Models\course_types;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Schema::disableForeignKeyConstraints();
        course_types::truncate();
        Course::truncate();
        Schema::enableForeignKeyConstraints();


        $html = course_types::create([
            "name" => "Meine erste Website in HTML & CSS",
            "image" => "https://hacker-school.de/wp-content/uploads/2022/06/yourschool-html-736x444.jpg",
            "duration" => 4
        ]);

        $python = course_types::create([
            "name" => "Grundlagen in Python",
            "image" => "https://hacker-school.de/wp-content/uploads/2022/06/yourschool-python-736x444.jpg",
            "duration" => 4
        ]);

        $scratch = course_types::create([
            "name" => "Spieleprogrammierung: Flappy Bird in Scratch",
            "image" => "https://hacker-school.de/wp-content/uploads/2022/09/Kursbilder-Webseite-1-736x444.jpg",
            "duration" => 4
        ]);


        for($i = 0; $i < 4; $i++){
            Course::create([
                "description" => "In diesem Kurs lernst du die Grundlagen von HTML und CSS kennen. Du wirst deine erste eigene Website erstellen und sie mit CSS gestalten. Am Ende des Kurses kannst du deine Website mit deinen Freunden teilen und sie im Internet veröffentlichen.",
                "course_type_id" => $html->id,
                "duration" => $html->duration,
                "start_date" => $this->return_random_date(),
            ]);
            Course::create([
                "description" => "In diesem Kurs lernst du die Grundlagen von Python kennen. Du wirst dein erstes eigenes Programm schreiben und es mit Python ausführen. Am Ende des Kurses kannst du dein Programm mit deinen Freunden teilen und es im Internet veröffentlichen.",
                "course_type_id" => $python->id,
                "duration" => $python->duration,
                "start_date" => $this->return_random_date(),
            ]);
            Course::create([
                "description" => "In diesem Kurs lernst du die Grundlagen von Scratch kennen. Du wirst dein erstes eigenes Spiel programmieren und es mit Scratch ausführen. Am Ende des Kurses kannst du dein Spiel mit deinen Freunden teilen und es im Internet veröffentlichen.",
                "course_type_id" => $scratch->id,
                "duration" => $scratch->duration,
                "start_date" => $this->return_random_date(),
            ]);
        }

    }

    protected function return_random_date(){
        $start_date = strtotime("2022-10-01");
        $end_date = strtotime("2024-10-31");

        $timestamp = mt_rand($start_date, $end_date);
        $datetime = date("Y-m-d h:i:s", $timestamp);
        return $datetime;
    }
}
