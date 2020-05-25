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
            $this->tabs->extend(new DashboardAction())->disableLegacyExtensions();

            Notification::error('No dashboard or dashlet found');
        }

        $query = (new Select())
            ->columns('*')
            ->from('dashboard')
            ->where(['id = ?' => $this->tabs->getActiveName(), 'type = ?' => 'system']);

        $dashboard = $this->getDb()->select($query)->fetch();

        //Note: In private dashboards type, the dashlets are sorted by user_dashlets_order
        // and in system dashboards the private dashlets are always sorted according to the public dashlets
        //TODO: If you want to have the dashlets the other way around, you just have to adjust the function call orderBy
        $select = (new Select())
            ->columns('dashlet.*')
            ->joinLeft('dashlet_order do', 'do.dashlet_id = dashlet.id')
            ->joinLeft('dashlet_user_order duo', 'duo.dashlet_id = dashlet.id')
            ->from('dashlet')
            ->where([
                'dashlet.dashboard_id = ?' => $this->tabs->getActiveName(),
                'duo.username IS NULL OR duo.username = ?' => Auth::getInstance()->getUser()->getUsername()
            ])
            ->orderBy($dashboard ? 'do.order' : 'duo.order', 'DESC');

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
            ->columns('dashboard.*, do.`order`')
            ->from('dashboard')
            ->joinLeft('dashboard_order do', 'do.dashboard_id = dashboard.id')
            ->joinLeft('dashboard_user_order duo', 'duo.dashboard_id = dashboard.id')
            ->where([
                'dashboard.type = "system" OR dashboard.owner = ?' => Auth::getInstance()->getUser()->getUsername(),
                'duo.username IS NULL OR duo.username = ?' => Auth::getInstance()->getUser()->getUsername()
            ])
            ->orderBy('do.`order`, dashboard.id');

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
