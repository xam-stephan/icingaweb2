<?php

namespace Icinga\Module\Dashboards\Controllers;

use Icinga\Authentication\Auth;
use Icinga\Module\Dashboards\Common\Database;
use Icinga\Module\Dashboards\Web\Widget\DashboardSetting;
use Icinga\Module\Dashboards\Web\Controller;
use Icinga\Module\Dashboards\Web\Widget\Tabextension\DashboardAction;
use Icinga\Web\Url;
use ipl\Sql\Select;

class SettingsController extends Controller
{
    use Database;

    public function indexAction()
    {
        $this->createTabs();

        $query = (new Select())
            ->from('dashboard')
            ->columns('*')
            ->where([
                'type = "system" OR owner = ?'  => Auth::getInstance()->getUser()->getUsername()
            ])
            ->orderBy('dashboard.id');

        $dashboard = $this->getDb()->select($query);

        $this->content = new DashboardSetting($dashboard);
    }

    /**
     * Create a tab for each dashboard from the database
     *
     * @return \ipl\Web\Widget\Tabs
     */
    protected function createTabs()
    {
        $tabs = $this->getTabs();

        $select = (new Select())
            ->columns('*')
            ->from('dashboard')
            ->where([
                'dashboard.type = "system" OR dashboard.owner = ?' => Auth::getInstance()->getUser()->getUsername()
            ])
            ->orderBy('dashboard.id');

        $userDashboards = $this->getDb()->select($select);

        foreach ($userDashboards as $userDashboard) {
            $tabs->add($userDashboard->name, [
                'label' => $userDashboard->name,
                'url' => Url::fromPath('dashboards', [
                    'dashboard' => $userDashboard->id
                ])
            ])->extend(new DashboardAction())->disableLegacyExtensions();
        }

        return $tabs;
    }
}
