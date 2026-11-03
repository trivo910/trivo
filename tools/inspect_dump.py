"""Print schema names only; never print sensitive row contents."""
from pathlib import Path
import re

for name in ('u802165322_KeH3A.sql', 'u364121842_3Zy6A.sql'):
    print(name)
    with Path(name).open(encoding='utf-8', errors='replace') as source:
        for line in source:
            if line.startswith('CREATE TABLE'):
                print(line.strip())
            elif line.startswith('-- Generation Time:'):
                print(line.strip())
