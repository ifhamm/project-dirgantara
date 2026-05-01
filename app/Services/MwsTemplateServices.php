<?php

namespace App\Services;

class MwsTemplateServices
{
    public static function getTemplates()
    {
        return [
            'Repair' => [
                'Incoming Record',
                'Functional Test',
                'Fault Isolation',
                'Disassembly',
                'Cleaning',
                'Check',
                'Assembly',
                'Functional Test',
                'FOD Control',
                'Final Inspection'
            ],

            'Overhaul' => [
                'Incoming Record',
                'Functional Test',
                'Fault Isolation',
                'Disassembly',
                'Cleaning',
                'Check',
                'Assembly',
                'Functional Test',
                'FOD Control',
                'Final Inspection'
            ],

            'F.Test' => [
                'Incoming Record',
                'Functional Test',
                'Fault Isolation',
                'Disassembly',
                'Cleaning',
                'Check',
                'Assembly',
                'Functional Test',
                'FOD Control',
                'Final Inspection'
            ],
        ];
    }
}