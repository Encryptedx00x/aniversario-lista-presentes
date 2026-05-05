<?php
declare(strict_types=1);

namespace App\Helpers;

use App\Models\SettingsModel;

class SiteConfig {

    private const MONTHS_PT = [
        1  => 'janeiro',
        2  => 'fevereiro',
        3  => 'março',
        4  => 'abril',
        5  => 'maio',
        6  => 'junho',
        7  => 'julho',
        8  => 'agosto',
        9  => 'setembro',
        10 => 'outubro',
        11 => 'novembro',
        12 => 'dezembro',
    ];

    private const MONTHS_PT_LONG_WORDS = [
        1  => 'janeiro',  2 => 'fevereiro', 3  => 'março',
        4  => 'abril',    5 => 'maio',      6  => 'junho',
        7  => 'julho',    8 => 'agosto',    9  => 'setembro',
        10 => 'outubro', 11 => 'novembro', 12 => 'dezembro',
    ];

    private const NUMBERS_PT = [
        1  => 'um',     2  => 'dois',   3  => 'três',    4  => 'quatro',
        5  => 'cinco',  6  => 'seis',   7  => 'sete',    8  => 'oito',
        9  => 'nove',  10  => 'dez',   11  => 'onze',   12  => 'doze',
        13 => 'treze', 14  => 'quatorze', 15 => 'quinze', 16 => 'dezesseis',
        17 => 'dezessete', 18 => 'dezoito', 19 => 'dezenove', 20 => 'vinte',
        21 => 'vinte e um', 22 => 'vinte e dois', 23 => 'vinte e três',
        24 => 'vinte e quatro', 25 => 'vinte e cinco', 26 => 'vinte e seis',
        27 => 'vinte e sete', 28 => 'vinte e oito', 29 => 'vinte e nove',
        30 => 'trinta', 31 => 'trinta e um',
    ];

    public static function getPublicSettings(): array {
        $defaults = [
            'event_person_name'   => 'Maria',
            'event_person_gender' => 'm',                   // 'm' | 'f'
            'event_age'           => '30',
            'event_date'          => '2026-05-20',
            'event_location'      => 'São Paulo',
            'pix_name'            => 'Maria S. Andrade',
            'pix_key'             => 'maria.aniv@email.com',
            'pix_bank'            => '260 — Nu Pagamentos',
            'pix_city'            => 'São Paulo — SP',
            'pix_code'            => '00020126360014BR.GOV.BCB.PIX0114maria.aniv@email5204000053039865406150.005802BR5919MARIA S ANDRADE6009SAO PAULO62070503***6304B7C9',
        ];

        $stored = (new SettingsModel)->getMany(array_keys($defaults));
        $cfg    = array_merge($defaults, $stored);

        $name = trim((string) $cfg['event_person_name']);
        if ($name === '') {
            $name = $defaults['event_person_name'];
        }

        $age = (int) ($cfg['event_age'] ?? $defaults['event_age']);
        if ($age <= 0) {
            $age = (int) $defaults['event_age'];
        }

        $eventDate = (string) ($cfg['event_date'] ?? $defaults['event_date']);
        $eventTs   = strtotime($eventDate . ' 00:00:00');
        if ($eventTs === false) {
            $eventDate = $defaults['event_date'];
            $eventTs   = strtotime($eventDate . ' 00:00:00') ?: time();
        }

        $location = trim((string) $cfg['event_location']);
        if ($location === '') {
            $location = $defaults['event_location'];
        }

        $gender = strtolower(trim((string) ($cfg['event_person_gender'] ?? 'm')));
        $gender = ($gender === 'f') ? 'f' : 'm';
        $thanks = $gender === 'f' ? 'Obrigada' : 'Obrigado';

        $brandName     = $name . ', ' . $age . '.';
        $brandLocation = $location . ' · ' . self::formatDatePt($eventTs);

        $eventDay     = (int) date('j', $eventTs);
        $eventMonthIx = (int) date('n', $eventTs);
        $eventYear    = (int) date('Y', $eventTs);

        return [
            'eventPersonName'   => $name,
            'eventPersonGender' => $gender,
            'thanksWord'        => $thanks,
            'thanksWordLower'   => mb_strtolower($thanks, 'UTF-8'),
            'eventAge'          => $age,
            'eventDate'         => $eventDate,
            'eventLocation'     => $location,
            'eventTimestamp'  => $eventTs,
            'eventDayPt'      => self::NUMBERS_PT[$eventDay] ?? (string) $eventDay,
            'eventMonthPt'    => self::MONTHS_PT[$eventMonthIx] ?? '',
            'eventDayMonthPt' => (self::NUMBERS_PT[$eventDay] ?? (string) $eventDay)
                                    . ' de '
                                    . (self::MONTHS_PT[$eventMonthIx] ?? ''),
            'eventYear'       => $eventYear,
            'brandInitial'    => mb_strtoupper(mb_substr($name, 0, 1, 'UTF-8'), 'UTF-8'),
            'brandName'       => $brandName,
            'brandLocation'   => $brandLocation,
            'pixName'         => trim((string) $cfg['pix_name']),
            'pixKey'          => trim((string) $cfg['pix_key']),
            'pixBank'         => trim((string) $cfg['pix_bank']),
            'pixCity'         => trim((string) $cfg['pix_city']),
            'pixCode'         => trim((string) $cfg['pix_code']),
        ];
    }

    private static function formatDatePt(int $ts): string {
        $day   = (int) date('j', $ts);
        $month = (int) date('n', $ts);
        $year  = (int) date('Y', $ts);

        $monthName = self::MONTHS_PT[$month] ?? '';
        return $day . ' de ' . $monthName . ', ' . $year;
    }
}
