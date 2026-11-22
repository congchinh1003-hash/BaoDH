import re

with open('database/it_service_desk_backup_latest.sql', 'r', encoding='utf-8') as f:
    lines = f.readlines()

new_lines = []
alter_fks = []
current_table = None

for line in lines:
    m_tab = re.match(r'^CREATE TABLE `([a-zA-Z0-9_]+)`', line.strip())
    if m_tab:
        current_table = m_tab.group(1)
        new_lines.append(line)
        continue
    
    if current_table:
        m_fk = re.match(r'^\s*CONSTRAINT `(.*?)` FOREIGN KEY \((.*?)\) REFERENCES `(.*?)` \((.*?)\)(.*)', line.strip())
        if m_fk:
            fk_name = m_fk.group(1)
            col = m_fk.group(2)
            ref_tab = m_fk.group(3)
            ref_col = m_fk.group(4)
            action = m_fk.group(5).rstrip(',').strip()
            action_clause = f' {action}' if action else ''
            alter_fks.append(f'ALTER TABLE `{current_table}` ADD CONSTRAINT `{fk_name}` FOREIGN KEY ({col}) REFERENCES `{ref_tab}` ({ref_col}){action_clause};')
            continue
        
        if line.strip().startswith(') ENGINE='):
            idx = len(new_lines) - 1
            while idx >= 0 and not new_lines[idx].strip():
                idx -= 1
            if idx >= 0 and new_lines[idx].rstrip().endswith(','):
                new_lines[idx] = new_lines[idx].rstrip()[:-1] + '\n'
            current_table = None

    new_lines.append(line)

content = ''.join(new_lines)
with open('database/it_service_desk_tidb.sql', 'w', encoding='utf-8') as f:
    f.write(content)
    f.write('\n\n-- ==========================================================\n')
    f.write('-- FOREIGN KEYS ADDED AFTER ALL TABLES AND DATA ARE CREATED\n')
    f.write('-- ==========================================================\n\n')
    for alt in alter_fks:
        f.write(alt + '\n')

print(f'Done! Successfully generated database/it_service_desk_tidb.sql with {len(alter_fks)} foreign keys deferred to end.')
