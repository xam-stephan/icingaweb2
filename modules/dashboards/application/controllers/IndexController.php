<?php

namespace Icinga\Module\Dashboards\Controllers;

use Icinga\Authentication\Auth;
use Icinga\Module\Dashboards\Common\Database;
use Icinga\Module\Dashboards\Web\Controller;
use Icinga\Module\Dashboards\Web\Widget\Tabextension\DashboardAction;
use Icinga\Module\Dashboards\Web\Widget\DashboardWidget;
use Icinga\Web\Notification;
use Icinga\Web\Url;
use ipl\Sql\Select;

class IndexController extends Controller
{
    use Database;

    public function indexAction()
    {
        try {
            $this->createTabsAndAutoActivateDashboard();
        } catch (\Exception $e) {
            $this->createTabsAndAutoActivateDashboard();

            Notification::error("No dashboard or dashlet found!");
        }

        $select = (new Select())
            ->columns('dashlet.*')
            ->from('dashlet')
            ->where([
                'dashlet.dashboard_id = ?' => $this->tabs->getActiveName(),
            ]);

        $dashlets = $this->getDb()->select($select);

        $this->content = new DashboardWidget($dashlets);
    }

    /**
     * create Tabs and
     * activate the first dashboard from the database when the url $params doesn't given
     *
     * @return int
     *
     * @throws \Icinga\Exception\NotFoundError      If the database doesn't have a dashboard
     */
    protected function createTabsAndAutoActivateDashboard()
    {
        $tabs = $this->getTabs();

        $select = (new Select())
            ->columns('dashboard_home.*')
            ->from('dashboard_home')
            ->where(['dashboard_home.owner = ?' => Auth::getInstance()->getUser()->getUsername()]);

        $query = (new Select())
            ->columns('dashboard_home.*')
            ->from('dashboard_home')
            ->join('dashboard', 'dashboard.home_id = dashboard_home')
            ->join('dashboard_user', 'dashboard_user.dashboard_id = dashboard.id')
            ->where(['dashboard_user.user = ?' => Auth::getInstance()->getUser()->getUsername()]);

        $query->unionAll($select)
            ->joinLeft(
                'dashboard_home_order dho',
                ['dho.home = dashboard_home.name AND dho.user = ?' => Auth::getInstance()->getUser()->getUsername()])
            ->groupBy('dashboard_home.name, dho.`order`')
            ->orderBy('dho.`order`, dashboard_home.name');

        $dashboards = $this->getDb()->select($select);

        foreach ($dashboards as $dashboard) {
            $tabs->add($dashboard->id, [
                'label' => $dashboard->name,
                'url' => Url::fromPath('dashboards', [
                    'dashboard' => $dashboard->id
                ])
            ])->extend(new DashboardAction())->disableLegacyExtensions();

            $ids[] = $dashboard->id;
        }

        $id = $this->params->get('dashboard') ?: array_shift($ids);

        $tabs->activate($id);

        return $id;
    }
}
