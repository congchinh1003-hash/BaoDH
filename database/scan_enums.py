import re

with open('database/it_service_desk_tidb.sql', 'r', encoding='utf-8') as f:
    sql = f.read()

# Let's find all enum occurrences in CREATE TABLE
enum_defs = re.findall(r'`(\w+)`\s+enum\((.*?)\)', sql, re.IGNORECASE)
print("Enum definitions found:", len(enum_defs))
for col, vals in enum_defs:
    print(f"  {col}: {vals}")

# Also let's find any occurrences of ,'', in INSERT statements
empty_strings = re.findall(r'INSERT INTO `?(\w+)`? VALUES (.*?);', sql)
print(f"Found {len(empty_strings)} INSERT statements.")
for tbl, vals in empty_strings:
    if ",''," in vals:
        print(f"Table `{tbl}` contains empty string values: `,''`")
