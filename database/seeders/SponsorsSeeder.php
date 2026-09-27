<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Membership\Models\Season;
use Domain\Board\Models\Sponsor;
use Domain\Board\Models\Sponsorship;
use Domain\Board\Models\SponsorshipLevel;
use Illuminate\Database\Seeder;

class SponsorsSeeder extends Seeder
{
    public function run(): void
    {
        $season = Season::query()->latest('id')->first();
        $levels = SponsorshipLevel::all()->keyBy('name');

        $sponsorsByLevel = [
            'House' => [
                ['name' => 'Nowe NEL', 'website' => 'https://nelexcavation.ca/'],
                ['name' => 'Pine Brook Electric Ltd', 'website' => 'https://www.facebook.com/pinebrookelectric/'],
                ['name' => 'Ironworks Distillery', 'website' => null],
                ['name' => 'Wine Kings', 'website' => 'https://www.facebook.com/WineKingsInc/'],
            ],
            'Skip' => [
                ['name' => 'CKBW', 'website' => 'https://www.cjhk.ca/'],
            ],
            'Mate' => [
                ['name' => "Sabean's Drywall Ltd", 'website' => null],
                ['name' => 'CIBC Private Wealth Bridgewater', 'website' => 'https://www.woodgundy.cibc.com/en/home.html'],
                ['name' => 'The Curling Store', 'website' => 'https://thecurlingstore.com/'],
                ['name' => 'Bridgewater Pharmasave', 'website' => 'https://pharmasave.com/store/pharmasave-bridgewater/'],
                ['name' => 'Co-operators Insurance', 'website' => 'https://www.cooperators.ca/'],
                ['name' => "Rhyno's Ltd.", 'website' => 'https://rhynosltd.com/'],
                ['name' => 'Berg Industrial Service', 'website' => 'https://berg-group.com/'],
                ['name' => 'Steele Auto Group', 'website' => 'https://steeleauto.com/'],
            ],
            'Second' => [
                ['name' => 'Best Western Plus', 'website' => 'https://www.bestwestern.com/'],
                ['name' => 'Vogue Optical', 'website' => 'https://vogueoptical.com/'],
                ['name' => 'GoliathTech', 'website' => 'https://www.goliathtechpiles.com/'],
                ['name' => "CarStar, Emino's Autobody & Coll Ctr", 'website' => null],
                ['name' => "Jac's Burgers and Shakes", 'website' => null],
                ['name' => 'Seaside Hearing', 'website' => null],
                ['name' => 'Carver Estate Law and Litigation', 'website' => null],
                ['name' => 'South Shore Centre', 'website' => 'https://southshorecentre.com/'],
            ],
            'Lead' => [
                ['name' => 'Nova Insurance', 'website' => 'https://www.nova-insurance.ca/'],
                ['name' => 'Champion Rain Gutters', 'website' => 'https://www.championraingutter.ca/'],
                ['name' => 'Economy Appliance', 'website' => null],
                ['name' => 'Fresh Cuts Market', 'website' => 'https://www.freshcutsmarket.ca/'],
                ['name' => 'Nelson Monuments', 'website' => 'https://nelsonmonuments.com/'],
                ['name' => "O'Regan's South Shore", 'website' => null],
                ['name' => "Sam's No Frills", 'website' => 'https://www.nofrills.ca/'],
                ['name' => 'The River Pub', 'website' => null],
                ['name' => "Gow's Home Hardware", 'website' => 'https://www.gowshomehardware.ca/'],
                ['name' => 'Town and Country Property Improvements Ltd.', 'website' => null],
            ],
        ];

        collect($sponsorsByLevel)->each(function (array $sponsors, string $levelName) use ($levels, $season) {
            $level = $levels->get($levelName);

            collect($sponsors)->each(function (array $sponsorData) use ($level, $season) {
                $sponsor = Sponsor::create([
                    'name' => $sponsorData['name'],
                    'website' => $sponsorData['website'],
                    'is_active' => true,
                ]);

                if ($season && $level) {
                    Sponsorship::create([
                        'sponsor_id' => $sponsor->id,
                        'sponsorship_level_id' => $level->id,
                        'season_id' => $season->id,
                        'amount_cents' => $level->amount_cents,
                        'start_date' => $season->start_date ?? now()->startOfYear(),
                        'end_date' => $season->end_date ?? now()->endOfYear(),
                    ]);
                }
            });
        });
    }
}
