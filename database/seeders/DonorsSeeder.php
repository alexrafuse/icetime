<?php

declare(strict_types=1);

namespace Database\Seeders;

use Domain\Board\Models\Donor;
use Illuminate\Database\Seeder;

class DonorsSeeder extends Seeder
{
    public function run(): void
    {
        collect([
            'A1 Clarington',
            'Alexander (Sandy) Smith',
            'Allison Weagle',
            'Bob Forbes',
            'Bruce & Celeste Stoddart',
            'Canoe Financial',
            'Cheryl Murray',
            'CIBC Management',
            'Don and Dianne Stewart',
            "Dylan Day & Adrienne O'Neil",
            'Glenn Josephson',
            'Greg Lowe',
            'Jane Foster',
            'Jim Smith',
            'John & Judy Lawrence',
            'Judith Lawrence',
            'Karen Decker-Brien',
            'Laura Fultz',
            'Lynn Roberts',
            'Margaret Horne',
            'Maurita Croft',
            'Michele Hue',
            'Mike Croft',
            'Nancy McNairn',
            'Peggy Lewis',
            'Peter & Nancy McConnery',
            'Roger & Eileen Samson',
            'Ron & Jane Nickerson',
            'Ross Lee',
            'Scotia Wealth Management',
            'Scott & Donna Shears',
            'Sherman Creaser',
            'South Shore Ready Mix',
            'Steve & Sue Mills',
            'Translogic Distribution Services Ltd',
            'Vince Gendron',
            'Ward Beck',
            'Wayne & Connie Burke',
            'William Bent',
        ])->each(fn (string $name) => Donor::create(['name' => $name]));
    }
}
