<?php

namespace Database\Seeders;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\Institution;
use App\Models\Position;
use App\Models\Sponsor;
use App\Models\User;
use App\Models\Voter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        DB::table('site_settings')->insertOrIgnore([
            ['key' => 'youtube_url', 'value' => 'https://www.youtube.com/', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'instagram_url', 'value' => 'https://www.instagram.com/', 'created_at' => $now, 'updated_at' => $now],
        ]);
        DB::table('site_settings')->whereIn('key', ['youtube_url', 'instagram_url'])->whereNull('value')->update([
            'value' => DB::raw("CASE WHEN `key` = 'youtube_url' THEN 'https://www.youtube.com/' ELSE 'https://www.instagram.com/' END"),
            'updated_at' => $now,
        ]);

        $institution = Institution::updateOrCreate(['code' => 'KURAPOA-01'], [
            'name' => 'Chuo Kikuu cha Mfano',
            'is_active' => true,
        ]);

        Sponsor::query()
            ->where('name', 'Tume Huru ya Taifa ya Uchaguzi')
            ->update(['name' => 'Tume ya Taifa ya Uchaguzi']);

        foreach ([
            'Tume ya Taifa ya Uchaguzi',
            'Serengeti',
            'Kilimanjaro',
            'Ngorongoro',
        ] as $index => $sponsorName) {
            Sponsor::firstOrCreate(
                ['name' => $sponsorName],
                ['sort_order' => $index, 'is_active' => true],
            );
        }

        User::updateOrCreate(['email' => 'admin@kurapoa.test'], [
            'name' => 'Msimamizi KuraPoa',
            'password' => Hash::make('Password123!'),
            'is_admin' => true,
            'is_super_admin' => true,
            'role' => 'admin',
            'institution_id' => $institution->id,
        ]);

        $election = Election::updateOrCreate(['title' => 'Uchaguzi wa Serikali ya Wanafunzi 2026'], [
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(7),
            'status' => 'open',
            'institution_id' => $institution->id,
            'is_test' => true,
        ]);

        $ballot = [
            'Rais' => [
                ['name' => 'Asha Mussa', 'manifesto' => 'Kusimamia uwazi na kuboresha huduma za wanafunzi.'],
                ['name' => 'Juma Mwakalinga', 'manifesto' => 'Kujenga ushirikiano na kutetea maslahi ya kila mwanafunzi.'],
                ['name' => 'Neema James', 'manifesto' => 'Kupanua fursa za uongozi, ubunifu na maendeleo chuoni.'],
            ],
            'Makamu wa Rais' => [
                ['name' => 'Hassan Juma', 'manifesto' => 'Kusikiliza wanafunzi na kuimarisha mawasiliano ya chuo.'],
                ['name' => 'Rehema Ally', 'manifesto' => 'Kusaidia utekelezaji wa ahadi na uwajibikaji wa viongozi.'],
            ],
            'Katibu Mkuu' => [
                ['name' => 'Baraka Paulo', 'manifesto' => 'Kutoa taarifa sahihi na kutunza kumbukumbu kwa uwazi.'],
                ['name' => 'Zawadi Peter', 'manifesto' => 'Kuweka mawasiliano ya serikali ya wanafunzi wazi na kwa wakati.'],
            ],
        ];

        foreach ($ballot as $positionTitle => $candidateList) {
            $position = Position::firstOrCreate(
                ['election_id' => $election->id, 'title' => $positionTitle],
                ['sort_order' => array_search($positionTitle, array_keys($ballot), true)],
            );

            foreach ($candidateList as $candidateData) {
                Candidate::firstOrCreate(
                    ['position_id' => $position->id, 'name' => $candidateData['name']],
                    ['manifesto' => $candidateData['manifesto'], 'status' => 'approved'],
                );
            }
        }

        $codes = ['111111', '222222', '333333', '444444', '555555', '666666', '777777', '888888', '999999', '100000'];

        foreach ($codes as $index => $code) {
            $registrationNumber = sprintf('MOCU/2026/%03d', $index + 1);
            $name = [
                'Amina Joseph',
                'Peter Daniel',
                'Neema Selemani',
                'Baraka Issa',
                'Zawadi Mwinyi',
                'Hassan Daudi',
                'Rehema Ali',
                'Juma Salum',
                'Asha Paulo',
                'Mariam Juma',
            ][$index];

            Voter::firstOrCreate(
                ['election_id' => $election->id, 'reg_number' => $registrationNumber],
                [
                    'name' => $name,
                    'phone' => null,
                    'access_code' => Hash::make($code),
                    'access_code_fingerprint' => hash_hmac('sha256', $code, (string) config('app.key')),
                    'is_active' => true,
                    'verified_at' => now(),
                ],
            );
        }
    }
}
