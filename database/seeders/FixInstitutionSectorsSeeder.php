<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Institution;
use App\Models\Sector;

class FixInstitutionSectorsSeeder extends Seeder
{
    public function run(): void
    {
        // Exact EMIS → Sector mapping from Excel
       $emisSectorMap = [
    201=>'Urban-I', 202=>'Urban-I', 203=>'Urban-I', 204=>'Urban-I', 205=>'Urban-I',
    206=>'Urban-I', 207=>'Urban-I', 208=>'Urban-I', 209=>'Urban-I', 210=>'Urban-I',
    211=>'Urban-I', 212=>'Urban-I', 213=>'Urban-I', 214=>'Urban-I', 215=>'Urban-I',
    216=>'Urban-I', 217=>'Urban-I', 218=>'Urban-I', 219=>'Urban-I', 220=>'Urban-I',
    221=>'Urban-I', 222=>'Urban-I', 223=>'Urban-I', 224=>'Urban-I', 225=>'Urban-I',
    226=>'Urban-I', 227=>'Urban-I', 228=>'Urban-I', 229=>'Urban-I', 230=>'Urban-I',
    231=>'Urban-I', 232=>'Urban-I', 233=>'Urban-I', 234=>'Urban-I', 235=>'Urban-I',
    236=>'Urban-I', 237=>'Urban-I', 238=>'Urban-I', 239=>'Urban-I', 240=>'Urban-I',
    241=>'Urban-I', 242=>'Urban-I', 243=>'Urban-I', 244=>'Urban-I', 245=>'Urban-I',
    246=>'Urban-I', 247=>'Urban-I', 248=>'Urban-I', 249=>'Urban-I',
    250=>'Urban-II', 251=>'Urban-II', 252=>'Urban-II', 253=>'Urban-II', 254=>'Urban-II',
    255=>'Urban-II', 256=>'Urban-II', 257=>'Urban-II', 258=>'Urban-II', 259=>'Urban-II',
    260=>'Urban-II', 261=>'Urban-II', 262=>'Urban-II', 263=>'Urban-II', 264=>'Urban-II',
    265=>'Urban-II', 266=>'Urban-II', 267=>'Urban-II', 268=>'Urban-II', 269=>'Urban-II',
    270=>'Urban-II', 271=>'Urban-II', 272=>'Urban-II', 273=>'Urban-II', 274=>'Urban-II',
    275=>'Urban-II', 276=>'Urban-II', 277=>'Urban-II', 278=>'Urban-II', 279=>'Urban-II',
    280=>'Urban-II', 281=>'Urban-II', 282=>'Urban-II', 283=>'Urban-II', 284=>'Urban-II',
    285=>'Urban-II', 286=>'Urban-II', 287=>'Urban-II', 288=>'Urban-II', 289=>'Urban-II',
    290=>'Urban-II', 291=>'Urban-II', 292=>'Urban-II', 293=>'Urban-II', 294=>'Urban-II',
    295=>'Urban-I',  296=>'Urban-II', 297=>'Urban-I',  298=>'Urban-II', 299=>'Urban-II',
    300=>'Urban-I',  301=>'Urban-I',  302=>'Urban-II', 303=>'Urban-I',  304=>'Urban-I',
    305=>'Urban-I',  306=>'Urban-I',  307=>'Urban-I',  308=>'Urban-I',  309=>'Urban-II',
    310=>'Urban-II', 311=>'Urban-II', 312=>'Urban-II', 313=>'Urban-II', 314=>'Urban-II',
    315=>'Urban-I',  316=>'Urban-I',  317=>'Urban-I',
    401=>'B.K', 402=>'B.K', 403=>'B.K', 404=>'B.K', 405=>'B.K',
    406=>'B.K', 407=>'B.K', 408=>'B.K', 409=>'B.K', 410=>'B.K',
    411=>'B.K', 412=>'B.K', 413=>'B.K', 414=>'B.K', 415=>'B.K',
    416=>'B.K', 417=>'B.K', 418=>'B.K', 419=>'B.K', 420=>'B.K',
    421=>'B.K', 422=>'Tarnol', 423=>'B.K', 424=>'B.K', 425=>'B.K',
    426=>'B.K', 427=>'B.K', 428=>'B.K', 429=>'B.K', 430=>'B.K',
    431=>'B.K', 432=>'B.K', 433=>'B.K', 434=>'B.K', 435=>'B.K',
    436=>'B.K', 437=>'B.K', 438=>'B.K', 439=>'B.K', 440=>'B.K',
    441=>'B.K', 442=>'B.K', 443=>'B.K', 444=>'B.K', 445=>'B.K',
    446=>'B.K', 447=>'B.K', 448=>'B.K', 449=>'B.K', 450=>'B.K',
    451=>'B.K', 452=>'B.K', 453=>'B.K', 454=>'B.K', 455=>'B.K',
    456=>'B.K', 457=>'B.K', 458=>'B.K', 459=>'B.K', 460=>'B.K',
    461=>'B.K', 462=>'B.K', 463=>'B.K', 464=>'B.K', 465=>'B.K',
    466=>'B.K', 467=>'B.K', 468=>'B.K', 469=>'B.K', 470=>'B.K',
    471=>'B.K', 472=>'B.K', 473=>'B.K', 474=>'B.K', 475=>'B.K',
    476=>'B.K', 477=>'B.K', 478=>'B.K', 479=>'B.K',
    501=>'Sihala', 502=>'Sihala', 503=>'Sihala', 504=>'Sihala', 505=>'Sihala',
    506=>'Sihala', 507=>'Sihala', 508=>'Sihala', 509=>'Sihala', 510=>'Sihala',
    511=>'Sihala', 512=>'Sihala', 513=>'Sihala', 514=>'Sihala', 515=>'Sihala',
    516=>'Sihala', 517=>'Sihala', 518=>'Sihala', 519=>'Sihala', 520=>'Sihala',
    521=>'Sihala', 522=>'Sihala', 523=>'Sihala', 524=>'Sihala', 525=>'Sihala',
    526=>'Sihala', 527=>'Sihala', 528=>'Sihala', 529=>'Sihala', 530=>'Sihala',
    531=>'Sihala', 532=>'Sihala', 533=>'Sihala', 534=>'Sihala', 535=>'Sihala',
    536=>'Sihala', 537=>'Sihala', 538=>'Sihala', 539=>'Sihala', 540=>'Sihala',
    541=>'Sihala', 542=>'Sihala', 543=>'Sihala', 544=>'Sihala', 545=>'Sihala',
    546=>'Sihala', 547=>'Sihala', 548=>'Sihala', 549=>'Sihala', 550=>'Sihala',
    551=>'Sihala', 552=>'Sihala', 553=>'Sihala', 554=>'Sihala', 555=>'Sihala',
    556=>'Sihala', 557=>'Sihala', 558=>'Sihala', 559=>'Sihala', 560=>'Sihala',
    561=>'Sihala', 562=>'Sihala', 563=>'Sihala', 564=>'Sihala', 565=>'Sihala',
    566=>'Sihala', 567=>'Sihala', 568=>'Sihala', 569=>'Sihala', 570=>'Sihala',
    571=>'Sihala', 572=>'Sihala', 573=>'Sihala', 574=>'Sihala', 575=>'Sihala',
    601=>'Tarnol', 602=>'Tarnol', 603=>'Tarnol', 604=>'Tarnol', 605=>'Tarnol',
    606=>'Tarnol', 607=>'Tarnol', 608=>'Tarnol', 609=>'Tarnol', 610=>'Tarnol',
    611=>'Tarnol', 612=>'Tarnol', 613=>'Tarnol', 614=>'Tarnol', 615=>'Tarnol',
    616=>'Tarnol', 617=>'Tarnol', 618=>'Tarnol', 619=>'Tarnol', 620=>'Tarnol',
    621=>'Tarnol', 622=>'Tarnol', 623=>'Tarnol', 624=>'Tarnol', 625=>'Tarnol',
    626=>'Tarnol', 627=>'Tarnol', 628=>'Tarnol', 629=>'Tarnol', 630=>'Tarnol',
    631=>'Tarnol', 632=>'Tarnol', 633=>'Tarnol', 634=>'Tarnol', 635=>'Tarnol',
    636=>'Tarnol', 637=>'Tarnol', 638=>'Tarnol', 639=>'Tarnol', 640=>'Tarnol',
    641=>'Tarnol', 642=>'Tarnol', 643=>'Tarnol', 644=>'Tarnol', 645=>'Tarnol',
    646=>'Tarnol', 647=>'Tarnol', 648=>'Tarnol', 649=>'Tarnol', 650=>'Tarnol',
    651=>'Tarnol', 652=>'Tarnol', 653=>'Tarnol', 654=>'Tarnol', 655=>'Tarnol',
    701=>'Nilore', 702=>'Nilore', 703=>'Nilore', 704=>'Nilore', 705=>'Nilore',
    706=>'Nilore', 707=>'Nilore', 708=>'Nilore', 709=>'Nilore', 710=>'Nilore',
    711=>'Nilore', 712=>'Nilore', 713=>'Nilore', 714=>'Nilore', 715=>'Nilore',
    716=>'Nilore', 717=>'Nilore', 718=>'Nilore', 719=>'Nilore', 720=>'Nilore',
    721=>'Nilore', 722=>'Nilore', 723=>'Nilore', 724=>'Nilore', 725=>'Nilore',
    726=>'Nilore', 727=>'Nilore', 728=>'Nilore', 729=>'Nilore', 730=>'Nilore',
    731=>'Nilore', 732=>'Nilore', 733=>'Nilore', 734=>'Nilore', 735=>'Nilore',
    736=>'Nilore', 737=>'Nilore', 738=>'Nilore', 739=>'Nilore', 740=>'Nilore',
    741=>'Nilore', 742=>'Nilore', 743=>'Nilore', 744=>'Nilore', 745=>'Nilore',
    746=>'Nilore', 747=>'Nilore', 748=>'Nilore', 749=>'Nilore', 750=>'Nilore',
    751=>'Nilore', 752=>'Nilore', 753=>'Nilore', 754=>'Nilore', 755=>'Nilore',
    756=>'Nilore', 757=>'Nilore', 758=>'Nilore', 759=>'Nilore', 760=>'Nilore',
    761=>'Nilore', 762=>'Nilore', 763=>'Nilore', 764=>'Nilore', 765=>'Nilore',
    766=>'Nilore', 767=>'Nilore',
    801=>'Sihala',   802=>'Urban-II', 803=>'Urban-II', 804=>'Urban-II', 805=>'Urban-II',
    806=>'Urban-I',  807=>'Urban-II', 808=>'Sihala',   809=>'Urban-II', 810=>'Urban-I',
    811=>'Tarnol',   812=>'B.K',      813=>'Urban-II',
    901=>'Urban-II', 902=>'Urban-II', 903=>'Urban-II', 904=>'Urban-I',  905=>'Urban-I',
    906=>'Urban-II', 907=>'Urban-II', 908=>'Urban-I',  909=>'Urban-II', 910=>'Urban-II',
    911=>'Urban-II', 912=>'Urban-I',  913=>'Urban-I',  914=>'Urban-I',  915=>'Urban-I',
    916=>'Urban-II', 917=>'Urban-II', 918=>'Urban-II', 919=>'Urban-II', 920=>'Sihala',
    921=>'Tarnol',   922=>'Tarnol',   923=>'Tarnol',   924=>'Tarnol',   925=>'Sihala',
    926=>'B.K',
];
        // Cache sector IDs
        $sectorIds = Sector::pluck('id', 'name')->toArray();

        $updated = 0;
        foreach ($emisSectorMap as $emis => $sectorName) {
            $sectorId = $sectorIds[$sectorName] ?? null;
            if (!$sectorId) {
                $this->command->warn("Sector not found: {$sectorName}");
                continue;
            }

            $rows = Institution::where('code', (string)$emis)
                ->where('sector_id', '!=', $sectorId)
                ->update(['sector_id' => $sectorId]);

            $updated += $rows;
        }

        $this->command->info("Fixed sector assignments for {$updated} institutions.");

        // Show final counts
        $counts = \DB::table('institutions')
            ->join('sectors', 'institutions.sector_id', '=', 'sectors.id')
            ->select('sectors.name', \DB::raw('count(*) as total'))
            ->groupBy('sectors.name')
            ->orderBy('sectors.name')
            ->get();

        foreach ($counts as $row) {
            $this->command->info("  {$row->name}: {$row->total}");
        }
    }
}
