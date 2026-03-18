-- 数据权限菜单节点补丁
-- 适用于已安装 SaiAdmin 的数据库，避免与初始化种子主键冲突。

INSERT INTO `sa_system_menu` (
  `parent_id`,
  `name`,
  `code`,
  `slug`,
  `type`,
  `path`,
  `component`,
  `method`,
  `icon`,
  `sort`,
  `link_url`,
  `is_iframe`,
  `is_keep_alive`,
  `is_hidden`,
  `is_fixed_tab`,
  `is_full_page`,
  `generate_id`,
  `generate_key`,
  `status`,
  `remark`,
  `created_by`,
  `updated_by`,
  `create_time`,
  `update_time`,
  `delete_time`
)
SELECT
  6,
  '数据权限',
  '',
  'core:role:data',
  3,
  '',
  '',
  NULL,
  '',
  100,
  '',
  2,
  2,
  2,
  2,
  2,
  0,
  NULL,
  1,
  NULL,
  1,
  1,
  '2026-01-01 00:00:00',
  '2026-01-01 00:00:00',
  NULL
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM `sa_system_menu` WHERE `slug` = 'core:role:data'
);
