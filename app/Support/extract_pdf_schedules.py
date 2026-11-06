import sys
import re
import json
import pdfplumber

KNOWN_ROOMS = [
    'LAB 1', 'LAB 2', 'LAB 3', 'LAB 4', 'LAB 5', 'LAB 6', 'LAB 7', 'LAB 8', 'LAB 9',
    'LAB 10', 'LAB 11', 'LAB 12', 'LAB 13', 'LAB 14', 'LAB 15', 'LAB 16', 'LAB 17', 'LAB 18',
    'LAB 19', 'LAB 20', 'LAB 21', 'LAB 22', 'LAB IPA', 'LAB PB',
    'RUANG 1', 'RUANG 2', 'RUANG 3', 'RUANG 4', 'RUANG 5', 'RUANG 6', 'RUANG 7', 'RUANG 8', 'RUANG 9',
    'RUANG 10', 'RUANG 11', 'RUANG 12', 'RUANG 13', 'RUANG 14', 'RUANG 15', 'RUANG 16', 'RUANG 17', 'RUANG 18',
    'RUANG 19', 'RUANG 20', 'RUANG 21', 'RUANG 22', 'RUANG 23', 'RUANG 24', 'RUANG 25', 'RUANG 26', 'RUANG 27',
    'RUANG 28', 'RUANG 29', 'RUANG 30', 'RUANG 31', 'RUANG 32', 'RUANG 33', 'RUANG 34',
    'HALL', 'SINIAR', 'OR1', 'OR2', 'OR3', 'OR'
]

CLASS_PATTERN = re.compile(r'\b(X|XI|XII)\s*(AKL|AK|BD|BR|DKV|LPS|MP|RPL|TKJ)\s*(\d+)?\b', re.IGNORECASE)

FOOTER_PATTERNS = [
    re.compile(r'\bFET\s*\d.*', re.IGNORECASE),
    re.compile(r'\bJadwal dihasilkan.*', re.IGNORECASE),
    re.compile(r'\bKembali ke atas\b', re.IGNORECASE),
    re.compile(r'\b\d{1,2}\/\d{1,2}\/\d{2,4}\b.*'),
]

def clean_subject_text(text):
    s = text
    for p in FOOTER_PATTERNS:
        s = p.sub('', s)
    s = re.sub(r'---|-x-', '', s)
    s = re.sub(r'\s+', ' ', s).strip(" -,\t\"\'")
    return s if s else "Pelajaran"

def normalize_class(text):
    m = CLASS_PATTERN.search(text)
    if m:
        level = m.group(1).upper()
        major = m.group(2).upper()
        rombel = m.group(3)
        normalized = f"{level} {major}" + (f" {rombel}" if rombel else "")
        return normalized, m.start(), m.end()
    return None, -1, -1

def get_column_boundaries(v_lines):
    clusters = []
    for v in sorted(v_lines):
        if not clusters or v - clusters[-1][-1] > 4.0:
            clusters.append([v])
        else:
            clusters[-1].append(v)
    return [sum(c) / len(c) for c in clusters]

def parse_class_cell(words_in_cell, class_name):
    words_in_cell.sort(key=lambda w: (round(w['top']/4), w['x0']))
    text = " ".join([w['text'] for w in words_in_cell]).strip()
    
    if not text or text in ['---', '-x-'] or re.match(r'^(\s*---?\s*)+$', text) or re.match(r'^(\s*-x-?\s*)+$', text):
        return None
        
    room = None
    subj_part = text
    for kr in KNOWN_ROOMS:
        pat = re.compile(r'\b' + kr.replace(' ', r'\s*') + r'\b', re.IGNORECASE)
        if pat.search(subj_part):
            room = kr
            subj_part = pat.sub(' ', subj_part)
            break

    teacher = None
    tm = re.search(r'"(\d{1,3})[.,\s]*([A-Za-z][A-Za-z\s.,\'\-]+?)(?:"|$)', subj_part)
    if tm:
        teacher = tm.group(2).strip().rstrip(' ."\'')
        subj_part = subj_part.replace(tm.group(0), ' ')
    else:
        tm2 = re.search(r'\b(\d{1,3})[.,\s]+([A-Za-z][A-Za-z\s.,\'\-]+?)(?:$|\s+(?:LAB|RUANG|OR|HALL))', subj_part)
        if tm2:
            teacher = tm2.group(2).strip().rstrip(' ."\'')
            subj_part = subj_part.replace(tm2.group(0), ' ')
        else:
            tm3 = re.search(r'"?(\d{1,3})[.,\s]+([A-Za-z][^"\d]+)', subj_part)
            if tm3:
                teacher = tm3.group(2).strip().rstrip(' ."\'')
                subj_part = subj_part.replace(tm3.group(0), ' ')
            
    clean_subj = clean_subject_text(subj_part)
    if not teacher:
        return None
        
    return {
        'teacher': teacher,
        'class': class_name,
        'subject': clean_subj,
        'room': room
    }

def extract_schedules(pdf_file_path):
    all_extracted = []
    
    with pdfplumber.open(pdf_file_path) as pdf:
        for page_idx, page in enumerate(pdf.pages):
            words = page.extract_words()
            if not words:
                continue
                
            top_words = [w for w in words if w['top'] < 65]
            top_text = " ".join([w['text'] for w in top_words])
            
            day_headers = {}
            for w in words:
                if 40 <= w['top'] <= 85:
                    w_text = w['text'].capitalize()
                    if w_text in ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat']:
                        day_headers[w_text] = w
                        
            if len(day_headers) < 5:
                continue
                
            time_words = [w for w in words if w['x0'] < 75 and re.match(r'^\d\d:\d\d$', w['text'])]
            time_words.sort(key=lambda w: w['top'])
            if len(time_words) < 5:
                continue
                
            tm = re.search(r'"(\d+[.,\s]*[^"]+)"', top_text)
            header_class, _, _ = normalize_class(top_text)
            
            senin = day_headers['Senin']
            selasa = day_headers['Selasa']
            rabu = day_headers['Rabu']
            kamis = day_headers['Kamis']
            jumat = day_headers['Jumat']
            time_col_right = max([tw['x1'] for tw in time_words])
            
            days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat']
            
            # Determine column bounds
            v_lines = sorted(list(set([round(r['x0'], 1) for r in page.rects if r['width'] < 1.0 and r['height'] > 20])))
            v_bounds = get_column_boundaries(v_lines)
            
            if len(v_bounds) == 7:
                page_day_cols = {
                    'Senin': (v_bounds[1], v_bounds[2]),
                    'Selasa': (v_bounds[2], v_bounds[3]),
                    'Rabu': (v_bounds[3], v_bounds[4]),
                    'Kamis': (v_bounds[4], v_bounds[5]),
                    'Jumat': (v_bounds[5], v_bounds[6]),
                }
            else:
                page_day_cols = {
                    'Senin': (time_col_right + 2.0, (senin['x1'] + selasa['x0']) / 2.0),
                    'Selasa': ((senin['x1'] + selasa['x0']) / 2.0, (selasa['x1'] + rabu['x0']) / 2.0),
                    'Rabu': ((selasa['x1'] + rabu['x0']) / 2.0, (rabu['x1'] + kamis['x0']) / 2.0),
                    'Kamis': ((rabu['x1'] + kamis['x0']) / 2.0, (kamis['x1'] + jumat['x0']) / 2.0),
                    'Jumat': ((kamis['x1'] + jumat['x0']) / 2.0, 580.0),
                }

            if header_class and not tm:
                # ==========================================
                # FORMAT B: JADWAL KELAS (Class Timetable)
                # ==========================================
                all_h_borders = sorted(list(set([round(r['top'], 1) for r in page.rects if r['height'] < 1.0 and r['width'] > 20 and 70.0 <= r['top'] <= 560.0])))
                table_top = all_h_borders[0] if all_h_borders else 80.0
                table_bot = all_h_borders[-1] if all_h_borders else (time_words[-1]['top'] + 35.0)
                total_slots = len(time_words)
                slot_h = (table_bot - table_top) / total_slots
                
                for day_name in days:
                    x_left, x_right = page_day_cols[day_name]
                    
                    h_lines = []
                    for r in page.rects:
                        if r['height'] < 1.0 and r['width'] > 20:
                            if r['x0'] >= x_left - 5 and r['x1'] <= x_right + 5:
                                h_lines.append(round(r['top'], 1))
                    h_lines = sorted(list(set(h_lines)))
                    h_lines = [y for y in h_lines if y >= table_top - 5.0 and y <= table_bot + 5.0]
                    
                    if len(h_lines) >= 2:
                        for i in range(len(h_lines) - 1):
                            c_top = h_lines[i]
                            c_bot = h_lines[i+1]
                            
                            s_start = max(1, min(total_slots, int(round((c_top - table_top) / slot_h)) + 1))
                            s_end = max(1, min(total_slots, int(round((c_bot - table_top) / slot_h))))
                            if s_start > s_end:
                                s_end = s_start
                                
                            cell_words = [w for w in words if x_left <= (w['x0'] + w['x1'])/2.0 < x_right and c_top <= w['top'] < c_bot]
                            parsed = parse_class_cell(cell_words, header_class)
                            if parsed:
                                parsed['day'] = day_name
                                parsed['start_slot'] = s_start
                                parsed['end_slot'] = s_end
                                all_extracted.append(parsed)
            elif tm:
                # ==========================================
                # FORMAT A: JADWAL GURU (Teacher Timetable)
                # ==========================================
                raw_teacher = tm.group(1).strip()
                clean_teacher = re.sub(r'^\d+[.,\s]*', '', raw_teacher).rstrip('.')
                
                bounds = []
                for i in range(len(time_words) - 1):
                    bounds.append((time_words[i]['top'] + time_words[i+1]['top']) / 2.0)
                    
                slot_ranges = []
                for i, tw in enumerate(time_words):
                    s_top = bounds[i-1] if i > 0 else bounds[0] - 50.0
                    s_bot = bounds[i] if i < len(bounds) else time_words[-1]['top'] + 35.0
                    slot_ranges.append((i + 1, s_top, s_bot))
                    
                grid = {day: {slot: [] for slot, _, _ in slot_ranges} for day in page_day_cols}
                
                for w in words:
                    top = w['top']
                    cx = (w['x0'] + w['x1']) / 2.0
                    
                    matched_slot = None
                    for slot, s_top, s_bot in slot_ranges:
                        if s_top <= top < s_bot:
                            matched_slot = slot
                            break
                    if not matched_slot:
                        continue
                        
                    matched_day = None
                    for day, (c_left, c_right) in page_day_cols.items():
                        if c_left <= cx < c_right:
                            matched_day = day
                            break
                    if not matched_day:
                        continue
                        
                    grid[matched_day][matched_slot].append(w)
                    
                for day in days:
                    s = 1
                    while s <= len(slot_ranges):
                        slot_words = sorted(grid[day][s], key=lambda w: (round(w['top']/10), w['x0']))
                        slot_text = " ".join([w['text'] for w in slot_words]).strip()
                        
                        if not slot_words or slot_text in ['---', '-x-'] or re.match(r'^(\s*---?\s*)+$', slot_text) or re.match(r'^(\s*-x-?\s*)+$', slot_text):
                            s += 1
                            continue
                            
                        found_class, _, _ = normalize_class(slot_text)
                        
                        if found_class:
                            start_slot = s
                            end_slot = s
                            all_lesson_words = list(slot_words)
                            
                            while end_slot + 1 <= len(slot_ranges):
                                next_words = grid[day][end_slot + 1]
                                next_text = " ".join([w['text'] for w in next_words]).strip()
                                if not next_words or next_text in ['---', '-x-'] or re.match(r'^(\s*---?\s*)+$', next_text) or re.match(r'^(\s*-x-?\s*)+$', next_text):
                                    break
                                
                                next_class, _, _ = normalize_class(next_text)
                                if next_class:
                                    break
                                
                                all_lesson_words.extend(next_words)
                                end_slot += 1
                                    
                            all_lesson_words.sort(key=lambda w: (round(w['top']/10), w['x0']))
                            combined_text = " ".join([w['text'] for w in all_lesson_words])
                            
                            room = None
                            for kr in KNOWN_ROOMS:
                                pattern = re.compile(r'\b' + kr.replace(' ', r'\s*') + r'\b', re.IGNORECASE)
                                if pattern.search(combined_text):
                                    room = kr
                                    break
                                    
                            clean_subject = CLASS_PATTERN.sub('', combined_text)
                            if room:
                                room_pattern = re.compile(r'\b' + room.replace(' ', r'\s*') + r'\b', re.IGNORECASE)
                                clean_subject = room_pattern.sub('', clean_subject)
                                
                            clean_subject = clean_subject_text(clean_subject)
                                
                            all_extracted.append({
                                'teacher': clean_teacher,
                                'day': day,
                                'start_slot': start_slot,
                                'end_slot': end_slot,
                                'class': found_class,
                                'subject': clean_subject,
                                'room': room
                            })
                            
                            s = end_slot + 1
                        else:
                            s += 1

    return all_extracted

if __name__ == '__main__':
    if len(sys.argv) < 2:
        print("Usage: python extract_pdf_schedules.py <path_to_pdf>", file=sys.stderr)
        sys.exit(1)
        
    pdf_path = sys.argv[1]
    schedules = extract_schedules(pdf_path)
    print(json.dumps(schedules))
