import re

with open('database/it_service_desk_tidb.sql', 'r', encoding='utf-8') as f:
    sql = f.read()

# Let's fix row 5 of asset_handovers:
# (5,6,'',NULL,NULL,'Kho IT - Kệ A2' -> (5,6,'STATUS_CHANGE',NULL,NULL,'Kho IT - Kệ A2'
old_val = "(5,6,'',NULL,NULL,'Kho IT - Kệ A2','Kho IT - Kệ A2','Đổi trạng thái sang MAINTENANCE: Đưa vào phòng bảo trì sửa bàn phím',3,'2026-09-20 18:49:45')"
new_val = "(5,6,'STATUS_CHANGE',NULL,NULL,'Kho IT - Kệ A2','Kho IT - Kệ A2','Đổi trạng thái sang MAINTENANCE: Đưa vào phòng bảo trì sửa bàn phím',3,'2026-09-20 18:49:45')"

if old_val in sql:
    sql = sql.replace(old_val, new_val)
    print("Fixed asset_handovers row 5 empty enum!")
else:
    print("Could not find exact row 5 pattern, checking substring...")
    sql = sql.replace("(5,6,'',", "(5,6,'STATUS_CHANGE',")
    print("Replaced (5,6,'', with (5,6,'STATUS_CHANGE',")

# Let's check other tables with ENUMs:
# Enums in system:
# action_type, status, change_type, risk_level, environment_stage, protocol, os_type, issue_type, priority, sprint_status, test_type, test_execution_status, bug_severity, role, project_type, resolution_unit, link_type, ticket_type, impact, urgency, support_tier, approval_status, sla_status.

# Check if any other table has `,'',` at an enum column
with open('database/it_service_desk_tidb.sql', 'w', encoding='utf-8') as f:
    f.write(sql)

print("Saved updated database/it_service_desk_tidb.sql")
