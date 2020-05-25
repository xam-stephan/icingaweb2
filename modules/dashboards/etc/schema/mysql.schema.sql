DROP TABLE IF EXISTS dashboard_order;
DROP TABLE IF EXISTS dashlet_order;
DROP TABLE IF EXISTS dashboard_user_order;
DROP TABLE IF EXISTS dashlet_user_order;
DROP TABLE IF EXISTS  dashlet;
DROP TABLE IF EXISTS dashboard;

CREATE TABLE dashboard (
    id int(10) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name varchar(64) NOT NULL COLLATE utf8mb4_unicode_ci,
    type enum('system', 'private') NOT NULL,
    owner varchar(254) DEFAULT NULL COLLATE utf8mb4_unicode_ci
) Engine=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE dashlet (
    id int(10) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
    dashboard_id int(10) unsigned NOT NULL,
    name varchar(64) NOT NULL COLLATE utf8mb4_unicode_ci,
    url varchar(2048) NOT NULL,
    type enum('system', 'private') NOT NULL,
    owner varchar(254) DEFAULT NULL COLLATE utf8mb4_unicode_ci,
    CONSTRAINT fk_dashlet_dashboard FOREIGN KEY (dashboard_id) REFERENCES dashboard (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE dashboard_order (
    dashboard_id int(10) unsigned NOT NULL,
    `order` tinyint unsigned NOT NULL,
    CONSTRAINT fk_dashboard_order_dashboard FOREIGN KEY (dashboard_id) REFERENCES dashboard (id) ON DELETE CASCADE ON UPDATE CASCADE
) Engine=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE Table dashlet_order (
    dashlet_id int(10) unsigned NOT NULL,
    `order` tinyint unsigned NOT NULL,
    CONSTRAINT fk_dashlet_order_dashlet FOREIGN KEY (dashlet_id) REFERENCES dashlet (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE dashboard_user_order (
    dashboard_id int(10) unsigned NOT NULL,
    username varchar(254) NOT NULL COLLATE utf8mb4_unicode_ci,
    `order` tinyint unsigned NOT NULL,
    CONSTRAINT fk_user_dashboard_order_dashboard FOREIGN KEY (dashboard_id) REFERENCES dashboard(id) ON DELETE CASCADE ON UPDATE CASCADE
) Engine=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE dashlet_user_order (
    dashlet_id int(10) unsigned NOT NULL,
    username varchar(254) NOT NULL COLLATE utf8mb4_unicode_ci,
    `order` tinyint unsigned NOT NULL,
    CONSTRAINT fk_user_dashlet_order_dashlet FOREIGN KEY (dashlet_id) REFERENCES dashlet (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
