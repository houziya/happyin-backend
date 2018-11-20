ALTER TABLE order_splitting ADD fetched VARCHAR(64) DEFAULT NULL COMMENT 'cursor used to fetch this order';
CREATE TABLE manufacture_order (
  order_child_id int(11) NOT NULL COMMENT 'order_child_id from table order_splitting',
  order_id int(11) NOT NULL COMMENT 'order_id from table order_splitting',
  code int(11) NOT NULL COMMENT 'code from table order_splitting',
  fetch_cursor varchar(255) NOT NULL COMMENT 'cursor used to fetch orders',
  fetch_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'fetch time of this order',
  PRIMARY KEY (order_child_id, fetch_cursor),
  KEY idx_fetch_cursor_code (fetch_cursor, code)
) ENGINE=InnoDB CHARSET=utf8;

CREATE TABLE manufacture_order_archive (
  order_child_id int(11) NOT NULL,
  order_id int(11) NOT NULL,
  code int(11) NOT NULL, 
  fetch_cursor varchar(255) NOT NULL,
  fetch_time timestamp NOT NULL,
  archive_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'archive time of this order',
  PRIMARY KEY (order_child_id, fetch_cursor),
  KEY idx_archive_time_fetch_cursor_code (archive_time, fetch_cursor, code)
) ENGINE=InnoDB CHARSET=utf8;
