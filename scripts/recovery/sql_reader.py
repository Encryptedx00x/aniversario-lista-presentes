"""Read SQL backups without executing them; only gift rows leave the parser."""
import re

def statements(text):
    buf=[]; quote=None; i=0
    while i < len(text):
        c=text[i]
        if quote:
            buf.append(c)
            if c=='\\' and i+1<len(text): i+=1; buf.append(text[i])
            elif c==quote:
                if i+1<len(text) and text[i+1]==quote: i+=1; buf.append(text[i])
                else: quote=None
        elif c in "'\"`": quote=c; buf.append(c)
        elif text.startswith('/*',i):
            end=text.find('*/',i+2); i=len(text) if end<0 else end+1; buf.append(' ')
        elif text.startswith('--',i) and (i+2==len(text) or text[i+2].isspace()):
            end=text.find('\n',i); i=len(text) if end<0 else end; buf.append('\n')
        elif c=='#':
            end=text.find('\n',i); i=len(text) if end<0 else end; buf.append('\n')
        elif c==';':
            s=''.join(buf).strip();buf=[]
            if s: yield s
        else: buf.append(c)
        i+=1
    if ''.join(buf).strip(): yield ''.join(buf).strip()

def fields(text):
    out=[];start=0;quote=None;depth=0;i=0
    while i<len(text):
        c=text[i]
        if quote:
            if c=='\\':i+=1
            elif c==quote:
                if i+1<len(text) and text[i+1]==quote:i+=1
                else:quote=None
        elif c in "'\"`":quote=c
        elif c=='(':depth+=1
        elif c==')':depth-=1
        elif c==',' and depth==0:out.append(text[start:i].strip());start=i+1
        i+=1
    out.append(text[start:].strip());return out

def value(s):
    if s.upper()=='NULL':return None
    if re.fullmatch(r'-?\d+',s):return int(s)
    if re.fullmatch(r'-?\d+\.\d+',s):return s
    if len(s)>1 and s[0]==s[-1]=="'":
        s=s[1:-1].replace("''", "'")
        return re.sub(r'\\(.)',lambda m:{'n':'\n','r':'\r','t':'\t','0':'\0','b':'\b'}.get(m[1],m[1]),s)
    return s

def read_tables(text, allowed=('gifts', 'reservations')):
    """Decode explicit-column INSERT VALUES only; never execute source SQL."""
    result={name:[] for name in allowed}
    for statement in statements(text):
        m=re.match(r'INSERT(?: IGNORE)? INTO\s+`?([\w]+)`?\s*\((.*?)\)\s+VALUES\s*(.*)',statement,re.I|re.S)
        if not m or m[1] not in allowed:continue
        columns=[x.strip(' `\n\r\t') for x in fields(m[2])]
        for row in fields(m[3]):
            if not row.startswith('(') or not row.endswith(')'):raise ValueError('Unsupported INSERT expression')
            values=[value(x) for x in fields(row[1:-1])]
            if len(columns)!=len(values):raise ValueError('INSERT column count mismatch')
            result[m[1]].append(dict(zip(columns,values)))
    return result
