import re

with open('database/it_service_desk_tidb.sql', 'r', encoding='utf-8') as f:
    sql = f.read()

# Parse all CREATE TABLE definitions
tables = {}
for m in re.finditer(r'CREATE TABLE `(\w+)` \((.*?)\)\s*ENGINE=', sql, re.DOTALL):
    tbl_name = m.group(1)
    body = m.group(2)
    col_names = []
    enum_map = {} # col_index -> list of valid enums
    idx = 0
    for line in body.split('\n'):
        line = line.strip()
        m_col = re.match(r'`(\w+)`\s+(enum\((.*?)\)|[a-zA-Z0-9_]+)', line, re.IGNORECASE)
        if m_col and not line.startswith(('PRIMARY', 'KEY', 'UNIQUE', 'CONSTRAINT')):
            col_name = m_col.group(1)
            col_names.append(col_name)
            if 'enum(' in line.lower():
                m_enum = re.search(r'enum\((.*?)\)', line, re.IGNORECASE)
                vals = [v.strip().strip("'") for v in m_enum.group(1).split(',')]
                enum_map[idx] = (col_name, vals)
            idx += 1
    tables[tbl_name] = {'cols': col_names, 'enums': enum_map}

print(f"Parsed {len(tables)} tables.")

# Now parse all INSERT statements
# Simple SQL values parser
def parse_tuples(values_str):
    tuples = []
    current = []
    in_str = False
    str_char = None
    curr_val = []
    paren_depth = 0
    escaped = False
    
    for ch in values_str:
        if escaped:
            curr_val.append(ch)
            escaped = False
            continue
        if ch == '\\':
            curr_val.append(ch)
            escaped = True
            continue
        if ch in ("'", '"'):
            if not in_str:
                in_str = True
                str_char = ch
            elif str_char == ch:
                in_str = False
                str_char = None
            curr_val.append(ch)
            continue
        if in_str:
            curr_val.append(ch)
            continue
        if ch == '(':
            paren_depth += 1
            if paren_depth == 1:
                current = []
                curr_val = []
                continue
        if ch == ')':
            paren_depth -= 1
            if paren_depth == 0:
                current.append(''.join(curr_val).strip())
                tuples.append(current)
                current = []
                curr_val = []
                continue
        if ch == ',' and paren_depth == 1:
            current.append(''.join(curr_val).strip())
            curr_val = []
            continue
        curr_val.append(ch)
    return tuples

errors = 0
for m in re.finditer(r'INSERT INTO `?(\w+)`? VALUES (.*?);', sql, re.DOTALL):
    tbl_name = m.group(1)
    vals_str = m.group(2)
    if tbl_name not in tables:
        continue
    tbl_info = tables[tbl_name]
    tuples = parse_tuples(vals_str)
    for row_idx, row in enumerate(tuples):
        for col_idx, (col_name, allowed_vals) in tbl_info['enums'].items():
            if col_idx < len(row):
                val = row[col_idx]
                if val.upper() == 'NULL':
                    continue
                clean_val = val.strip("'")
                if clean_val not in allowed_vals:
                    print(f"ERROR in `{tbl_name}` row {row_idx+1}, col `{col_name}`: val '{clean_val}' not in {allowed_vals}")
                    errors += 1

if errors == 0:
    print("ALL ENUM VALUES IN ALL TABLES ARE 100% VALID!")
else:
    print(f"Total enum errors found: {errors}")
