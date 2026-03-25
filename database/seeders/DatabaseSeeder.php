<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\InternshipApplication;
use App\Models\Divisi;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call seeders to populate data
        $this->call([
            // DirektoratSeeder::class, // Disabled - using DivisiSeeder instead
            FieldOfInterestSeeder::class,
            DivisiSeeder::class,
            MentorUserSeeder::class,
        ]);


        // Create mentor (pembimbing) user for each divisi
        $mentors = [
            ['username' => 'mentor_divisi_penyaluran_dana', 'name' => 'Pembimbing Divisi Penyaluran Dana', 'email' => 'mentor_divisi_penyaluran_dana@posindonesia.co.id', 'password' => '$2y$12$FSrf3Ivcu.y0jGFEbMa./OR1EPG6d0tDosq.glpI8TOqPrvzfaWd.', 'divisi_id' => 125],
            ['username' => 'mentor_divisi_fronting_business', 'name' => 'Pembimbing Divisi Fronting Business', 'email' => 'mentor_divisi_fronting_business@posindonesia.co.id', 'password' => '$2y$12$U2i5G.OpZwa0TlNgZ6I4dedPX2sTGlvGZGw25bohfDOROagLdCknS', 'divisi_id' => 126],
            ['username' => 'mentor_divisi_financial_services_marketing', 'name' => 'Pembimbing Divisi Financial Services Marketing', 'email' => 'mentor_divisi_financial_services_marketing@posindonesia.co.id', 'password' => '$2y$12$ZJo5mJub5wSF7EFj3oIP5Opo7hCdyl.VO2US/bJfS1Aanlx0OOxMC', 'divisi_id' => 127],
            ['username' => 'mentor_divisi_payment', 'name' => 'Pembimbing Divisi Payment', 'email' => 'mentor_divisi_payment@posindonesia.co.id', 'password' => '$2y$12$9kuhY2wgisDtZyyOR1MdZuIzi8JWN7DK5njRquJUiddzKZBAIClnK', 'divisi_id' => 128],
            ['username' => 'mentor_divisi_digital_giro_and_payment_solution', 'name' => 'Pembimbing Divisi Digital Giro and Payment Solution', 'email' => 'mentor_divisi_digital_giro_and_payment_solution@posindonesia.co.id', 'password' => '$2y$12$yWvgpP9jcqdIvNruWmgC3uTXnm5doBGV7pLAyYbnCF3g0F5NsmbeG', 'divisi_id' => 129],
            ['username' => 'mentor_divisi_remittance_and_syariah_business', 'name' => 'Pembimbing Divisi Remittance and Syariah Business', 'email' => 'mentor_divisi_remittance_and_syariah_business@posindonesia.co.id', 'password' => '$2y$12$MPX0FD7gPcUBEILJj4qJgOWRb7z1ZQ5FhOiamDhhL.Rm0pksaScnG', 'divisi_id' => 130],
            ['username' => 'mentor_divisi_modern_channel_financial_services', 'name' => 'Pembimbing Divisi Modern Channel Financial Services', 'email' => 'mentor_divisi_modern_channel_financial_services@posindonesia.co.id', 'password' => '$2y$12$rMeQrnyvkmneziwfa3b6K.3RJdKwn7X6CwncWAQJ5hMbXd3YXU35K', 'divisi_id' => 131],
            ['username' => 'mentor_divisi_product_management', 'name' => 'Pembimbing Divisi Product Management', 'email' => 'mentor_divisi_product_management@posindonesia.co.id', 'password' => '$2y$12$zIVbsxp81OyhAL2YZbhDv.zKeeDK6A5RzJFVDFK1I.N/BUSS0UA5y', 'divisi_id' => 132],
            ['username' => 'mentor_divisi_account_management_and_corporate_marketing', 'name' => 'Pembimbing Divisi Account Management and Corporate Marketing', 'email' => 'mentor_divisi_account_management_and_corporate_marketing@posindonesia.co.id', 'password' => '$2y$12$5g7hhApkIX7LLs0HQjiC8uMTwMxn8KLCrF/3v/gUWMVehdbP/0Mo2', 'divisi_id' => 133],
            ['username' => 'mentor_divisi_project_management', 'name' => 'Pembimbing Divisi Project Management', 'email' => 'mentor_divisi_project_management@posindonesia.co.id', 'password' => '$2y$12$SeOtBN7UeM528mmZwJn2B.54UHz1GUULi1O/lkPuBl9cAnD.ahfG6', 'divisi_id' => 134],
            ['username' => 'mentor_divisi_bidding_and_collection_management', 'name' => 'Pembimbing Divisi Bidding and Collection Management', 'email' => 'mentor_divisi_bidding_and_collection_management@posindonesia.co.id', 'password' => '$2y$12$oZDu9TctGzLW.Soo8hnsg.DmaVDUNcSTbiGCBkh7ganxPuchJqw8m', 'divisi_id' => 135],
            ['username' => 'mentor_divisi_solution_partnership_business_planning_and_performance', 'name' => 'Pembimbing Divisi Solution, Partnership, Business Planning and Performance', 'email' => 'mentor_divisi_solution_partnership_business_planning_and_performance@posindonesia.co.id', 'password' => '$2y$12$kKM1XE7SNJP5oezdBjFlW.JKF5njRf6A/eTXsjZ0FOdhTkUhqdcT2', 'divisi_id' => 136],
            ['username' => 'mentor_divisi_digital_channel_posaja', 'name' => 'Pembimbing Divisi Digital Channel PosAja', 'email' => 'mentor_divisi_digital_channel_posaja@posindonesia.co.id', 'password' => '$2y$12$V7MnUkKi6nJXZ9TrXH9cTOceCYGjElll2y8Tj4P4KD98LvEx2crVW', 'divisi_id' => 137],
            ['username' => 'mentor_divisi_marketing_retail_business', 'name' => 'Pembimbing Divisi Marketing Retail Business', 'email' => 'mentor_divisi_marketing_retail_business@posindonesia.co.id', 'password' => '$2y$12$9dTPIBWT5A3.tIO1ccQFf.A0wS4tiPUJynu0JmcWR5ITswtb1IUwu', 'divisi_id' => 138],
            ['username' => 'mentor_divisi_penjualan_agenpos', 'name' => 'Pembimbing Divisi Penjualan Agenpos', 'email' => 'mentor_divisi_penjualan_agenpos@posindonesia.co.id', 'password' => '$2y$12$URqfx84x/94hawvb/5feXeFVfXHhUE23ZTaPzPthPtUbnviDwTUy2', 'divisi_id' => 139],
            ['username' => 'mentor_divisi_o_ranger', 'name' => 'Pembimbing Divisi O-Ranger', 'email' => 'mentor_divisi_o_ranger@posindonesia.co.id', 'password' => '$2y$12$xY2/bOp9vs0WovnA3Y92buJ6t2SmfoSsxxS3pE2kvmRbHsoT20oHy', 'divisi_id' => 140],
            ['username' => 'mentor_divisi_kemitraan_dan_solusi', 'name' => 'Pembimbing Divisi Kemitraan dan Solusi', 'email' => 'mentor_divisi_kemitraan_dan_solusi@posindonesia.co.id', 'password' => '$2y$12$DwF3otLqrqubAg6QRKptrubHK7.ZCV5K8//09UvJsUR9zvJlLIKem', 'divisi_id' => 141],
            ['username' => 'mentor_divisi_account_international_business', 'name' => 'Pembimbing Divisi Account International Business', 'email' => 'mentor_divisi_account_international_business@posindonesia.co.id', 'password' => '$2y$12$qk3DXnxX8r5VklO4YwKQ2utZdHFKXmudRhOCCmbo4aCmjZFJ30XsC', 'divisi_id' => 142],
            ['username' => 'mentor_divisi_wholesale_and_international_freight', 'name' => 'Pembimbing Divisi Wholesale and International Freight', 'email' => 'mentor_divisi_wholesale_and_international_freight@posindonesia.co.id', 'password' => '$2y$12$9GWTDeN5sq4K64Je9Ikp0u0OC/ajLyrF56pXeTLUC1IQ/DvMiKHAm', 'divisi_id' => 143],
            ['username' => 'mentor_divisi_courier_operation', 'name' => 'Pembimbing Divisi Courier Operation', 'email' => 'mentor_divisi_courier_operation@posindonesia.co.id', 'password' => '$2y$12$70PHFdVb1C84II6iG2af4.OP4VdmKnjXky2VDmClSnadMPNC16FK6', 'divisi_id' => 144],
            ['username' => 'mentor_divisi_digital_operation_and_quality_assurance', 'name' => 'Pembimbing Divisi Digital Operation and Quality Assurance', 'email' => 'mentor_divisi_digital_operation_and_quality_assurance@posindonesia.co.id', 'password' => '$2y$12$OMh6GeVHK3.GEIIPE9h6MeZUTdhrbqYKoSRI8TifSF6DwlyqEd5gK', 'divisi_id' => 145],
            ['username' => 'mentor_divisi_operation_cost_management_and_partnership', 'name' => 'Pembimbing Divisi Operation Cost Management and Partnership', 'email' => 'mentor_divisi_operation_cost_management_and_partnership@posindonesia.co.id', 'password' => '$2y$12$8.W2h34YP05yyTpflkLtQ.Wrz9repwWxFbn9VeMkQED1SNMvhQ.zy', 'divisi_id' => 146],
            ['username' => 'mentor_divisi_logistic_operation', 'name' => 'Pembimbing Divisi Logistic Operation', 'email' => 'mentor_divisi_logistic_operation@posindonesia.co.id', 'password' => '$2y$12$KZf8lT2c8Xw8.6JFDz7X7uI6Lczitlw/yTookFqabsvIhpA8W5N6K', 'divisi_id' => 147],
            ['username' => 'mentor_divisi_operation_control', 'name' => 'Pembimbing Divisi Operation Control', 'email' => 'mentor_divisi_operation_control@posindonesia.co.id', 'password' => '$2y$12$wXIRGf7VfG46xHbG0.wfuO0vosLmSkljB.3ZvidZMCMeO7ft6wZ4a', 'divisi_id' => 148],
            ['username' => 'mentor_divisi_networking_partnership_and_process_operation_development', 'name' => 'Pembimbing Divisi Networking, Partnership and Process Operation Development', 'email' => 'mentor_divisi_networking_partnership_and_process_operation_development@posindonesia.co.id', 'password' => '$2y$12$5rskrDhdkZHHMTgvIkK8oOBOmpY358seQ/TemuqDz/9Qiuaq84ySK', 'divisi_id' => 149],
            // divisi_id 150–186 (truncated in CSV preview — add from DivisiSeeder if needed)
            ['username' => 'mentor_divisi_human_capital_development_3', 'name' => 'Pembimbing Divisi Human Capital Development', 'email' => 'mentor_divisi_human_capital_development_3@posindonesia.co.id', 'password' => '$2y$12$IF4kMWd7a8K/n7sDeeKvs.8Zyqh8SiR7CfbjlLcxevxoHcwKL2pUi', 'divisi_id' => 170],
            ['username' => 'mentor_divisi_digital_learning_center_3', 'name' => 'Pembimbing Divisi Digital Learning Center', 'email' => 'mentor_divisi_digital_learning_center_3@posindonesia.co.id', 'password' => '$2y$12$HlHkN32iL4rgqs6peTjuBeTmNSNQiP55zK1/SEWMIiACm5T8S0d6O', 'divisi_id' => 171],
            ['username' => 'mentor_divisi_human_capital_business_partner_3', 'name' => 'Pembimbing Divisi Human Capital Business Partner', 'email' => 'mentor_divisi_human_capital_business_partner_3@posindonesia.co.id', 'password' => '$2y$12$2U5PNG0KT1iaLkzRawsiGe0QHo3lK1dVi5yQoZ8t.GmX.gUmeLz5u', 'divisi_id' => 172],
            ['username' => 'mentor_divisi_corporate_performance_3', 'name' => 'Pembimbing Divisi Corporate Performance', 'email' => 'mentor_divisi_corporate_performance_3@posindonesia.co.id', 'password' => '$2y$12$aPg45Rd5ErcgFbhGWov1Tu6ir2PRf6TN7jooIKlqIce93GSf9xHC.', 'divisi_id' => 173],
            ['username' => 'mentor_divisi_corporate_strategic_planning_and_synergy_business_3', 'name' => 'Pembimbing Divisi Corporate Strategic Planning and Synergy Business', 'email' => 'mentor_divisi_corporate_strategic_planning_and_synergy_business_3@posindonesia.co.id', 'password' => '$2y$12$WWCsYfKAdBGv5s//mjT0A.cwFKFX0F5SibepV.XbZaOZXjBs3PL8u', 'divisi_id' => 174],
            ['username' => 'mentor_divisi_business_development_innovation_and_incubation_3', 'name' => 'Pembimbing Divisi Business Development, Innovation and Incubation', 'email' => 'mentor_divisi_business_development_innovation_and_incubation_3@posindonesia.co.id', 'password' => '$2y$12$LAirob77a1TdhroRd8HBg.9PkfTlMl973tWsk8lcuSRCoiRCFZzVe', 'divisi_id' => 175],
            ['username' => 'mentor_divisi_customer_experience_3', 'name' => 'Pembimbing Divisi Customer Experience', 'email' => 'mentor_divisi_customer_experience_3@posindonesia.co.id', 'password' => '$2y$12$.x1YObotYPOYnLsTASTNEOmSvaFN8Nb0EueeHtDhI4w531Blma2Ry', 'divisi_id' => 176],
            ['username' => 'mentor_divisi_transformation_management_office_3', 'name' => 'Pembimbing Divisi Transformation Management Office', 'email' => 'mentor_divisi_transformation_management_office_3@posindonesia.co.id', 'password' => '$2y$12$WtlS54fZL6xzLMrl.sEGGOdRUOQL0/ldAC67hgwLPpmN7aCAr4HMG', 'divisi_id' => 177],
            ['username' => 'mentor_divisi_public_service_obligation_3', 'name' => 'Pembimbing Divisi Public Service Obligation', 'email' => 'mentor_divisi_public_service_obligation_3@posindonesia.co.id', 'password' => '$2y$12$etUfWgP1D1DbOg4qySNaOeo5O44dCYrDsqBgYuRxkGkJME0VxO2Fe', 'divisi_id' => 178],
            ['username' => 'mentor_divisi_corporate_communication_3', 'name' => 'Pembimbing Divisi Corporate Communication', 'email' => 'mentor_divisi_corporate_communication_3@posindonesia.co.id', 'password' => '$2y$12$qpl7b0NAVBuxzwumSetDd.DJeZn..NthdK9A5kHfeUTujr2oHW.EO', 'divisi_id' => 179],
            ['username' => 'mentor_divisi_legal_3', 'name' => 'Pembimbing Divisi Legal', 'email' => 'mentor_divisi_legal_3@posindonesia.co.id', 'password' => '$2y$12$czzBnaF0Oda8QR.qYjv8CeHy7vzAqp75JNYfwDoe4HoA9vw1zHYvO', 'divisi_id' => 180],
            ['username' => 'mentor_divisi_regulation_3', 'name' => 'Pembimbing Divisi Regulation', 'email' => 'mentor_divisi_regulation_3@posindonesia.co.id', 'password' => '$2y$12$b.tAf5K.l0nc8tXbY1eMFuldx1u3pkH.b.q1xqeVFoup9bjvrqhUm', 'divisi_id' => 181],
            ['username' => 'mentor_divisi_tanggung_jawab_sosial_dan_lingkungan_3', 'name' => 'Pembimbing Divisi Tanggung Jawab Sosial dan Lingkungan', 'email' => 'mentor_divisi_tanggung_jawab_sosial_dan_lingkungan_3@posindonesia.co.id', 'password' => '$2y$12$vsdbrK2rOJuqMRJwYwyaCO4ktFcTdamDf2kivWfEIrSo9RMBW/8Xq', 'divisi_id' => 182],
            ['username' => 'mentor_divisi_deputi_bidang_enabler_human_capital_umum_dan_keuangan_3', 'name' => 'Pembimbing Divisi Deputi Bidang Enabler (Human Capital, Umum dan Keuangan)', 'email' => 'mentor_divisi_deputi_bidang_enabler_human_capital_umum_dan_keuangan_3@posindonesia.co.id', 'password' => '$2y$12$ui1dI6V8XkpCAn.5MNCtyuOHFFJYK/0LKySBPFLjZ0NJ./OfpFV1y', 'divisi_id' => 183],
            ['username' => 'mentor_divisi_deputi_bidang_bisnis_layanan_keuangan_3', 'name' => 'Pembimbing Divisi Deputi Bidang Bisnis Layanan Keuangan', 'email' => 'mentor_divisi_deputi_bidang_bisnis_layanan_keuangan_3@posindonesia.co.id', 'password' => '$2y$12$9olcntbdtMXId3Q8./XBcOoBw.B53GjpAOde7LPNTjQhudvrFyRCy', 'divisi_id' => 184],
            ['username' => 'mentor_divisi_deputi_bidang_operasi_teknologi_informasi_dan_digital_solution_3', 'name' => 'Pembimbing Divisi Deputi Bidang Operasi, Teknologi Informasi, dan Digital Solution', 'email' => 'mentor_divisi_deputi_bidang_operasi_teknologi_informasi_dan_digital_solution_3@posindonesia.co.id', 'password' => '$2y$12$pAQdhtbhYLhdbXgv45VOLu2gg2.CcyQhjayI43LJH1hiFcO9Vj3/W', 'divisi_id' => 185],
            ['username' => 'mentor_divisi_deputi_bidang_bisnis_kurir_logistik_pos_internasional_dan_layanan_pos_universal_3', 'name' => 'Pembimbing Divisi Deputi Bidang Bisnis Kurir, Logistik, Pos Internasional, dan Layanan Pos Universal', 'email' => 'mentor_divisi_deputi_bidang_bisnis_kurir_logistik_pos_internasional_dan_layanan_pos_universal_3@posindonesia.co.id', 'password' => '$2y$12$9xeMKG2RhdLbH3AVgVwPb.hq7IDju5ReD611lbYSb7McLe9opv.EW', 'divisi_id' => 186],
        ];



        // Create peserta (intern) users from CSV
        $peserta = [
            [
                'username'    => 'raflee.malik@gmail.com',
                'name'        => 'Raflee Caesar Dano Malik',
                'email'       => 'raflee.malik@gmail.com',
                'password'    => '$2y$12$HMKq.El7JyZ4ZhlIO1rgrubMdh18HdnQpUAEjMQ44oYTemj8BcmQK',
                'nim'         => '1301223127',
                'university'  => 'Telkom University',
                'major'       => 'Teknik Informatika',
                'phone'       => '0819888028',
                'ktp_number'  => '1111111222222233',
                'role'        => 'peserta',
            ],
            [
                'username'    => 'raflee.amira@gmail.com',
                'name'        => null,
                'email'       => 'raflee.amira@gmail.com',
                'password'    => '$2y$12$jiV6aeU9irlm1S9Kn.HXQevOyMsIj4OD2zjVNXw2WviMySFJMkXxi',
                'nim'         => null,
                'university'  => null,
                'major'       => null,
                'phone'       => null,
                'ktp_number'  => null,
                'role'        => 'peserta',
            ],
        ];


        // Seeder peraturan default jika belum ada
        if (\App\Models\Rule::count() == 0) {
            \App\Models\Rule::create(['content' =>
                "1. Peserta wajib mematuhi seluruh peraturan dan tata tertib yang berlaku di PT Pos Indonesia.\n".
                "2. Peserta dilarang melakukan tindakan yang dapat merugikan perusahaan, baik secara langsung maupun tidak langsung.\n".
                "3. Peserta wajib menjaga kerahasiaan data dan informasi perusahaan.\n".
                "4. Peserta wajib hadir dan mengikuti seluruh kegiatan magang sesuai jadwal yang telah ditentukan.\n".
                "5. Peserta wajib menjaga sikap, perilaku, dan sopan santun selama berada di lingkungan perusahaan.\n".
                "6. Peserta dilarang menyalahgunakan fasilitas perusahaan untuk kepentingan pribadi.\n".
                "7. Peserta wajib melaporkan setiap kendala atau permasalahan kepada pembimbing/mentor magang.\n".
                "8. Peraturan dapat berubah sewaktu-waktu sesuai kebijakan perusahaan."
            ]);
        }
    }
}