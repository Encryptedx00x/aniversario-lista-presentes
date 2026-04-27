<?php
declare(strict_types=1);

namespace App\Helpers;

use App\Models\SettingsModel;

class SiteConfig {

    public static function getPublicSettings(): array {
        $defaults = [
            'event_person_name' => 'Maria',
            'event_age' => '30',
            'event_date' => '2026-06-12',
            'event_location' => 'São Paulo',
        ];

        $stored = (new SettingsModel)->getMany(array_keys($defaults));
        $cfg = array_merge($defaults, $stored);

        $name = trim((string) $cfg['event_person_name']);
        if ($name === '') {
            $name = $defaults['event_person_name'];
        }

        $age = (int) ($cfg['event_age'] ?? $defaults['event_age']);
        if ($age <= 0) {
            $age = (int) $defaults['event_age'];
        }

        $eventDate = (string) ($cfg['event_date'] ?? $defaults['event_date']);
        $eventTs = strtotime($eventDate . ' 00:00:00');
        if ($eventTs === false) {
            $eventDate = $defaults['event_date'];
            $eventTs = strtotime($eventDate . ' 00:00:00') ?: time();
        }

        $location = trim((string) $cfg['event_location']);
        if ($location === '') {
            $location = $defaults['event_location'];
        }

        $brandName = $name . ', ' . $age . '.';
        $brandLocation = $location . ' · ' . date('d \d\e F, Y', $eventTs);

        return [
            'eventPersonName' => $name,
            'eventAge' => $age,
            'eventDate' => $eventDate,
            'eventLocation' => $location,
            'eventTimestamp' => $eventTs,
            'brandInitial' => mb_strtoupper(mb_substr($name, 0, 1, 'UTF-8'), 'UTF-8'),
            'brandName' => $brandName,
            'brandLocation' => $brandLocation,
        ];
    }
}
