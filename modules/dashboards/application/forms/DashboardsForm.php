<?php

namespace Icinga\Module\Dashboards\Forms;

use Icinga\Authentication\Auth;
use Icinga\Module\Dashboards\Common\Database;
use ipl\Sql\Select;
use ipl\Web\Compat\CompatForm;

/**
 * Allows you to use the same form for editing and creating a dashboard or dashlet
 */
abstract class DashboardsForm extends CompatForm
{
    use Database;

    /**
     * Fetch all dashboards from the database and return them as array
     *
     * @return array
     */
    public function fetchDashboardsForSelectOption()
    {
        $dashboards = [];

        $select = (new Select())
            ->columns('*')
            ->from('dashboard')
            ->where([
                'dashboard.type = "system" OR dashboard.owner = ?' => Auth::getInstance()->getUser()->getUsername()
            ]);

        $result = $this->getDb()->select($select);

        foreach ($result as $dashboard) {
            $dashboards[$dashboard->id] = $dashboard->name;
        }

        return $dashboards;
    }

    /**
     * Create a new dashboard and return its id
     *
     * @param string $name
     *
     * @return int
     */
    public function createDashboard($name)
    {
        $data = [
            'name'  => $name,
            'type'  => $this->getValue('dashboard-type'),
            'owner' => $this->getValue('dashboard-type') === 'private'?
                Auth::getInstance()->getUser()->getUsername(): null
        ];

        $db = $this->getDb();
        $db->insert('dashboard', $data);

        return $db->lastInsertId();
    }

    /**
     * Check if the selected dashboard is private or not
     *
     * @param int $id   The id of the selected dashboard
     *
     * @return bool
     */
    public function checkForPrivateDashboard($id)
    {
        $select = (new Select())
            ->from('dashboard')
            ->columns('*')
            ->where([
                'id = ?' => $id,
                'type = ?' => 'private'
            ]);

        $dashboard = $this->getDb()->select($select)->fetch();

        if ($dashboard) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Display the FormElement for creating a new dashboards and dashlets
     */
    public function displayForm()
    {
        $this->addElement('textarea', 'url', [
            'label'         => 'Url',
            'placeholder'   => 'Enter Dashlet Url',
            'required'      => true,
            'rows'          => '3'
        ]);

        $this->addElement('text', 'name', [
            'label'         => 'Dashlet Name',
            'placeholder'   => 'Enter Dashlet Name',
            'required'      => true
        ]);

        $this->addElement('checkbox', 'new-dashboard', [
            'label'         => 'New Dashboard',
            'class'         => 'autosubmit',
        ]);

        $this->addElement('select', 'dashboard-type', [
            'label'         => 'Dashboard Type',
            'required'      => true,
            'options'       => [
                'system'    => 'system',
                'private'   => 'private'
            ]
        ]);

        if ($this->getElement('new-dashboard')->getValue() === 'y') {
            $this->addElement('text', 'new-dashboard-name', [
                'label'         => 'Dashboard Name',
                'placeholder'   => 'New Dashboard Name',
                'required'      => true,
            ]);
        } else {
            $this->addElement('select', 'dashboard', [
                'label'         => 'Dashboard',
                'required'      => true,
                'options'       => $this->fetchDashboardsForSelectOption()
            ]);
        }
    }
}
