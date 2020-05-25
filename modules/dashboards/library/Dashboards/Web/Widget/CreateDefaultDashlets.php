<?php

namespace Icinga\Module\Dashboards\Web\Widget;

use Icinga\Module\Dashboards\Common\Database;

class CreateDefaultDashlets
{
    use Database;

    public static function createAction()
    {
        (new CreateDefaultDashlets)->getDb()->insert('dashboard', [
            'name'      => 'Current Incidents',
            'type'      => 'system',
            'owner'     => null
        ]);

        (new CreateDefaultDashlets)->getDb()->insert('dashboard_order', [
            'dashboard_id'  => 1,
            '`order`'       => 1,
        ]);

        $data = [
            'dashboard_id'  => 1,
            'name'          => 'Service Problems',
            'url'           => '/icingaweb2/monitoring/list/services?service_problem=1&limit=10&sort=service_severity&',
            'type'          => 'system',
            'owner'         => null
        ];

        (new CreateDefaultDashlets)->getDb()->insert('dashlet', $data);

        $values = [
            'dashboard_id'  => 1,
            'name'          => 'Recently Recovered Services',
            'url'           => '/icingaweb2/monitoring/list/services?service_state=0
                                &limit=10&sort=service_last_state_change&dir=desc&',
            'type'          => 'system',
            'owner'         => null
        ];

        (new CreateDefaultDashlets)->getDb()->insert('dashlet', $values);

        $entries = [
            'dashboard_id'  => 1,
            'name'          => 'Host Problems',
            'url'           => '/icingaweb2/monitoring/list/hosts?host_problem=1&limit=10&sort=host_severity',
            'type'          => 'system',
            'owner'         => null
        ];

        (new CreateDefaultDashlets)->getDb()->insert('dashlet', $entries);

        (new CreateDefaultDashlets)->getDb()->insert('dashlet_order', [
            'dashlet_id'    => 1,
            '`order`'       => 1,
        ]);

        (new CreateDefaultDashlets)->getDb()->insert('dashlet_order', [
            'dashlet_id'    => 2,
            '`order`'       => 2,
        ]);

        (new CreateDefaultDashlets)->getDb()->insert('dashlet_order', [
            'dashlet_id'    => 3,
            '`order`'       => 3,
        ]);
    }
}
