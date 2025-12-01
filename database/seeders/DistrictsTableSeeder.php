<?php

namespace Database\Seeders;

use App\Models\Region;
use App\Models\District;
use Illuminate\Database\Seeder;

class DistrictsTableSeeder extends Seeder
{
    public function run()
    {
        $districtsByRegion = [
            'Dar es Salaam' => ['Ilala', 'Kinondoni', 'Temeke', 'Kigamboni', 'Ubungo'],
            'Arusha' => ['Arusha City', 'Arumeru', 'Karatu', 'Monduli', 'Longido', 'Ngorongoro'],
            'Dodoma' => ['Dodoma Urban', 'Bahi', 'Chamwino', 'Chemba', 'Kondoa', 'Kongwa', 'Mpwapwa'],
            'Kilimanjaro' => ['Hai', 'Moshi Urban', 'Moshi Rural', 'Mwanga', 'Rombo', 'Same', 'Siha'],
            'Tanga' => ['Handeni', 'Kilindi', 'Korogwe', 'Lushoto', 'Mkinga', 'Muheza', 'Pangani', 'Tanga City'],
            'Morogoro' => ['Gairo', 'Kilombero', 'Kilosa', 'Malinyi', 'Morogoro Rural', 'Morogoro Urban', 'Mvomero', 'Ulanga'],
            'Pwani' => ['Bagamoyo', 'Chalinze', 'Kibaha', 'Kibiti', 'Kisarawe', 'Mafia', 'Mkuranga', 'Rufiji'],
            'Lindi' => ['Kilwa', 'Lindi Urban', 'Lindi Rural', 'Liwale', 'Nachingwea', 'Ruangwa'],
            'Mtwara' => ['Masasi', 'Mtwara Urban', 'Mtwara Rural', 'Nanyumbu', 'Newala', 'Tandahimba'],
            'Ruvuma' => ['Mbinga', 'Nyasa', 'Songea Urban', 'Songea Rural', 'Tunduru'],
            'Iringa' => ['Iringa Urban', 'Iringa Rural', 'Kilolo', 'Mafinga', 'Mufindi'],
            'Mbeya' => ['Busokelo', 'Chunya', 'Kyela', 'Mbarali', 'Mbeya City', 'Mbeya Rural', 'Rungwe'],
            'Singida' => ['Ikungi', 'Iramba', 'Manyoni', 'Mkalama', 'Singida Urban', 'Singida Rural'],
            'Tabora' => ['Igunga', 'Kaliua', 'Nzega', 'Sikonge', 'Tabora Urban', 'Urambo', 'Uyui'],
            'Rukwa' => ['Kalambo', 'Nkasi', 'Sumbawanga Urban', 'Sumbawanga Rural'],
            'Kigoma' => ['Buhigwe', 'Kakonko', 'Kasulu', 'Kasulu Urban', 'Kibondo', 'Kigoma Urban', 'Uvinza'],
            'Shinyanga' => ['Kahama', 'Kishapu', 'Shinyanga Urban', 'Shinyanga Rural'],
            'Kagera' => ['Biharamulo', 'Bukoba Rural', 'Bukoba Urban', 'Karagwe', 'Kyerwa', 'Missenyi', 'Muleba', 'Ngara'],
            'Mwanza' => ['Ilemela', 'Kwimba', 'Magu', 'Misungwi', 'Nyamagana', 'Sengerema', 'Ukerewe'],
            'Mara' => ['Bunda', 'Butiama', 'Musoma Rural', 'Musoma Urban', 'Rorya', 'Serengeti', 'Tarime'],
            'Manyara' => ['Babati', 'Hanang', 'Kiteto', 'Mbulu', 'Simanjiro'],
            'Njombe' => ['Ludewa', 'Makambako', 'Njombe Urban', 'Njombe Rural', 'Wanging\'ombe'],
            'Katavi' => ['Mlele', 'Mpanda Urban', 'Mpanda Rural'],
            'Simiyu' => ['Bariadi', 'Busega', 'Itilima', 'Maswa', 'Meatu'],
            'Geita' => ['Bukombe', 'Chato', 'Geita', 'Mbogwe', 'Nyang\'hwale'],
            'Songwe' => ['Ileje', 'Mbozi', 'Momba', 'Songwe'],
            'Pemba North' => ['Micheweni', 'Wete'],
            'Pemba South' => ['Chake Chake', 'Mkoani'],
            'Unguja North' => ['Kaskazini A', 'Kaskazini B'],
            'Unguja South' => ['Kusini', 'Kati'],
            'Unguja Urban West' => ['Magharibi', 'Mjini']
        ];

        foreach ($districtsByRegion as $regionName => $districts) {
            $region = Region::where('name', $regionName)->first();
            if ($region) {
                foreach ($districts as $districtName) {
                    District::create([
                        'region_id' => $region->id,
                        'name' => $districtName,
                    ]);
                }
            }
        }
    }
}
