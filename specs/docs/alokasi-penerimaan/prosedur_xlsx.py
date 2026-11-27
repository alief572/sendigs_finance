# Helper gaya workbook prosedur, mengikuti specs/input/Prosedur Pembelian Non Operation.xlsx
# (kolom A..I, judul seksi biru tua, header abu, jurnal biru muda, input kuning, debit hijau, kredit merah).
# Salinan file ini juga ada di specs/docs/alokasi-penerimaan/ (dokumen per fitur dipisah).
import math
from openpyxl.styles import Font, PatternFill, Alignment, Border, Side

WIDTHS = {'A': 6, 'B': 42, 'C': 11, 'D': 19, 'E': 44, 'F': 15, 'G': 36, 'H': 15, 'I': 15}
SEC = PatternFill('solid', fgColor='1F4E78')
GRAY = PatternFill('solid', fgColor='D9D9D9')
JRN = PatternFill('solid', fgColor='DDEBF7')
INP = PatternFill('solid', fgColor='FFF2CC')
DR = PatternFill('solid', fgColor='C6E0B4')
CR = PatternFill('solid', fgColor='FF9999')
NOCOA = PatternFill('solid', fgColor='FFFF00')
thin = Side(style='thin', color='BFBFBF')
BOX = Border(left=thin, right=thin, top=thin, bottom=thin)
NUM = '#,##0;-#,##0;"-"'
COLS = 'ABCDEFGHI'
ISI = '(diisi user)'  # hal yang belum diketahui: dikosongkan untuk diisi user, sel kuning


def setup(ws):
    for k, v in WIDTHS.items():
        ws.column_dimensions[k].width = v
    ws.sheet_view.showGridLines = False


def _w(c1, c2):
    return sum(WIDTHS[COLS[i]] for i in range(COLS.index(c1), COLS.index(c2) + 1))


def _lines(text, width):
    if text is None:
        return 1
    return sum(max(1, math.ceil(len(part) / max(1, width * 0.95))) for part in str(text).split('\n'))


def cell(ws, r, c1, c2, val, bold=False, fill=None, color=None, num=False, center=False, border=True):
    if val == ISI and fill is None:
        fill = INP
    c = ws[f'{c1}{r}']
    c.value = val
    c.font = Font(bold=bold, color=color)
    c.alignment = Alignment(wrap_text=True, vertical='top', horizontal='center' if center else None)
    if num:
        c.number_format = NUM
    for col in COLS[COLS.index(c1):COLS.index(c2) + 1]:
        x = ws[f'{col}{r}']
        if fill:
            x.fill = fill
        if border:
            x.border = BOX
    if c2 != c1:
        ws.merge_cells(f'{c1}{r}:{c2}{r}')
    return _lines(val, _w(c1, c2))


def height(ws, r, n):
    ws.row_dimensions[r].height = max(15, 15 * n)


def title(ws, r, text):
    cell(ws, r, 'A', 'I', text, bold=True, border=False)


def section(ws, r, text):
    cell(ws, r, 'A', 'I', text, bold=True, fill=SEC, color='FFFFFF')


def konsep(ws, r, rows):
    for label, text in rows:
        n = max(cell(ws, r, 'B', 'B', label, bold=True), cell(ws, r, 'C', 'I', text))
        height(ws, r, n)
        r += 1
    return r


def steps(ws, r, rows):
    """rows: (no, proses, pic, keterangan, jurnal). Jurnal selain 'Tidak Ada Jurnal' ditebalkan biru muda."""
    for col, c2, h in (('A', 'A', 'No'), ('B', 'B', 'Proses'), ('C', 'D', 'PIC'), ('E', 'E', 'Keterangan'), ('F', 'I', 'Jurnal yang terbentuk')):
        cell(ws, r, col, c2, h, bold=True, fill=GRAY)
    r += 1
    for no, proses, pic, ket, jur in rows:
        j = jur != 'Tidak Ada Jurnal'
        n = max(cell(ws, r, 'A', 'A', no), cell(ws, r, 'B', 'B', proses), cell(ws, r, 'C', 'D', pic),
                cell(ws, r, 'E', 'E', ket), cell(ws, r, 'F', 'I', jur, bold=j, fill=JRN if j else None))
        height(ws, r, n)
        r += 1
    return r


def table(ws, r, spans, header, rows, bold_first=True):
    """Tabel bebas: spans = [('A','B'), ('C','E'), ...]."""
    for (c1, c2), h in zip(spans, header):
        cell(ws, r, c1, c2, h, bold=True, fill=GRAY)
    r += 1
    for row in rows:
        n = max(cell(ws, r, c1, c2, v, bold=(bold_first and i == 0)) for i, ((c1, c2), v) in enumerate(zip(spans, row)))
        height(ws, r, n)
        r += 1
    return r


def inputs(ws, r, rows, caption='Input simulasi (ubah angka kuning)'):
    """rows: (key, label, value). Mengembalikan peta key -> '$C$n' untuk dipakai di rumus."""
    cell(ws, r, 'B', 'B', caption, bold=True, border=False)
    r += 1
    ref = {}
    for key, label, val in rows:
        cell(ws, r, 'B', 'B', label)
        c = ws[f'C{r}']
        c.value = val
        c.fill = INP
        c.border = BOX
        c.number_format = '0.0%' if isinstance(val, float) and val < 1 else NUM
        ref[key] = f'$C${r}'
        r += 1
    return r, ref


def journal_header(ws, r):
    for col, h in zip('ABCDEFGHI', ('No', 'Jurnal', 'Langkah', 'PIC', 'Case', 'COA', 'Nama Account', 'Debit', 'Credit')):
        cell(ws, r, col, col, h, bold=True, fill=GRAY)
    return r + 1


def journal(ws, r, code, name, step, pic, case, lines, ref):
    """lines: (coa, akun, debit, kredit); debit/kredit = rumus dengan {key} dari input, atau None."""
    start = r
    for coa, acc, dr, kr in lines:
        cell(ws, r, 'F', 'F', coa, fill=NOCOA if coa == '(belum ada)' else None)
        cell(ws, r, 'G', 'G', acc)
        cell(ws, r, 'H', 'H', ('=' + dr.format(**ref)) if dr else None, fill=DR if dr else None, num=True)
        cell(ws, r, 'I', 'I', ('=' + kr.format(**ref)) if kr else None, fill=CR if kr else None, num=True)
        r += 1
    end = r - 1
    for col, val, b in (('A', code, True), ('B', name, True), ('C', step, False), ('D', pic, False), ('E', case, False)):
        cell(ws, start, col, col, val, bold=b)
        for rr in range(start + 1, end + 1):
            ws[f'{col}{rr}'].border = BOX
        if end > start:
            ws.merge_cells(f'{col}{start}:{col}{end}')
    need = max(_lines(case, WIDTHS['E']), _lines(name, WIDTHS['B']), _lines(pic, WIDTHS['D']))
    per = max(1, math.ceil(need / (end - start + 1)))
    for rr in range(start, end + 1):
        height(ws, rr, per)
    cell(ws, r, 'A', 'G', 'Balancing', bold=True, fill=GRAY)
    cell(ws, r, 'H', 'H', f'=SUM(H{start}:H{end})', bold=True, fill=GRAY, num=True)
    cell(ws, r, 'I', 'I', f'=SUM(I{start}:I{end})', bold=True, fill=GRAY, num=True)
    return r + 1


def note(ws, r, text):
    n = cell(ws, r, 'A', 'I', text, border=False)
    ws[f'A{r}'].font = Font(italic=True, color='595959')
    height(ws, r, n)
    return r + 1
