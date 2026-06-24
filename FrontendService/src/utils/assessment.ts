import {
  DEFAULT_METRIC_COLOR,
  DEFAULT_STATUS_COLOR,
  METRIC_RULES,
  STATUS_COLOR_RULES,
  type CurrencyOption,
  type MetricKey,
} from '@/constants/assessment'
import type { EvaluationData } from '@/services/evaluation'
import { getImageUrl } from '@/composables/useApi'

const curveDown = (x: number, a: number, b: number) => {
  if (x <= a) return 1
  if (x >= b) return 0
  return (b - x) / (b - a)
}

const curveUp = (x: number, a: number, b: number) => {
  if (x <= a) return 0
  if (x >= b) return 1
  return (x - a) / (b - a)
}

const curveTriangle = (x: number, a: number, b: number, c: number) => {
  if (x <= a || x >= c) return 0
  if (x === b) return 1
  if (x > a && x < b) return (x - a) / (b - a)
  return (c - x) / (c - b)
}

export const statusTextColor = (status: string) => {
  const normalized = status.toLowerCase()
  for (const rule of STATUS_COLOR_RULES) {
    const matches = rule.keywords.some((keyword) => normalized.includes(keyword))
    const blocked = rule.exclude?.some((keyword) => normalized.includes(keyword))
    if (matches && !blocked) {
      return rule.color
    }
  }

  return DEFAULT_STATUS_COLOR
}

export const formatCurrency = (value: number | null | undefined, currency: CurrencyOption) => {
  if (value === null || value === undefined) return '-'

  return new Intl.NumberFormat(currency.locale, {
    style: 'currency',
    currency: currency.code,
    maximumFractionDigits: 0,
  }).format(value ?? 0)
}

export const formatNumber = (value: number | null | undefined, locale: string = 'id-ID') => {
  if (value === null || value === undefined) return ''
  return new Intl.NumberFormat(locale).format(value)
}

export const formatCurrencySimple = (value: number | null | undefined) => {
  if (value === null || value === undefined) return '-'

  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(value ?? 0)
}

export const metricColor = (value: number, metric: MetricKey) => {
  const numeric = Number(value)
  if (Number.isNaN(numeric)) return DEFAULT_METRIC_COLOR

  const rules = METRIC_RULES[metric]
  const rendah = curveDown(numeric, rules.rendah[0], rules.rendah[1])
  const tinggi = curveUp(numeric, rules.tinggi[0], rules.tinggi[1])
  const normal = rules.normal
    ? curveTriangle(numeric, rules.normal[0], rules.normal[1], rules.normal[2])
    : 0

  const maxValue = Math.max(rendah, normal, tinggi)
  if (maxValue === tinggi) return '#047857'
  if (maxValue === normal) return '#b45309'
  if (maxValue === rendah) return '#be123c'
  return DEFAULT_METRIC_COLOR
}

export const scoreDash = (score: number) => {
  const clamped = Math.max(0, Math.min(100, score))
  return `${clamped} 100`
}

export const statusColor = (status: string) => {
  const s = status.toLowerCase()
  if (s.includes('tidak') || s.includes('buruk') || s.includes('kurang')) return { bg: '#ffe4e6', text: '#be123c', hex: '#be123c' }
  if (s.includes('cukup')) return { bg: '#fef3c7', text: '#b45309', hex: '#b45309' }
  if (s.includes('layak')) return { bg: '#d1fae5', text: '#047857', hex: '#047857' }
  return { bg: '#e3e2e6', text: '#43474e', hex: '#43474e' }
}

const preloadImage = (url: string): Promise<string> => {
  return new Promise((resolve) => {
    const img = new Image()
    img.onload = () => {
      const c = document.createElement('canvas')
      c.width = img.naturalWidth
      c.height = img.naturalHeight
      const ctx = c.getContext('2d')!
      ctx.drawImage(img, 0, 0)
      resolve(c.toDataURL('image/jpeg', 0.7))
    }
    img.onerror = () => resolve('')
    img.src = url
  })
}

const buildReportHTML = async (data: EvaluationData) => {
  const fmt = (v: number | null | undefined) => {
    if (v == null) return '-'
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v)
  }

  const conditionLabel = (score: number): { label: string; color: string; bg: string } => {
    if (score >= 80) return { label: 'Baik', color: '#047857', bg: '#d1fae5' }
    if (score >= 60) return { label: 'Cukup Baik', color: '#b45309', bg: '#fef3c7' }
    if (score >= 40) return { label: 'Cukup', color: '#b45309', bg: '#fef3c7' }
    if (score >= 20) return { label: 'Kurang', color: '#be123c', bg: '#ffe4e6' }
    return { label: 'Buruk', color: '#be123c', bg: '#ffe4e6' }
  }

  const ramLabel = (gb: number): { label: string; color: string; bg: string } => {
    if (gb >= 16) return { label: 'Tinggi', color: '#047857', bg: '#d1fae5' }
    if (gb >= 8) return { label: 'Sedang', color: '#b45309', bg: '#fef3c7' }
    return { label: 'Rendah', color: '#be123c', bg: '#ffe4e6' }
  }

  const statusPalette = (s: string): { text: string; bg: string } => {
    const lower = s.toLowerCase()
    if (lower.includes('tidak')) return { text: '#be123c', bg: '#ffe4e6' }
    if (lower.includes('cukup')) return { text: '#b45309', bg: '#fef3c7' }
    return { text: '#047857', bg: '#d1fae5' }
  }

  const badge = (label: string, bg: string, color: string) =>
    `<div style="inline:flex; items:center;padding:0 10px;height:27px;border-radius:999px;font-size:10px;font-weight:700;background:${bg};color:${color};white-space:nowrap;">${label}</div>`

  const statusBadge = (label: string, textColor: string, bg: string) =>
    `<div style="inline:flex; items:center;padding:0 16px;height:30px;border-radius:999px;font-size:12px;font-weight:700;background:${bg};color:${textColor};letter-spacing:0.3px;">${label}</div>`

  const date = new Date(data.created_at || new Date()).toLocaleDateString('id-ID', {
    day: '2-digit', month: 'long', year: 'numeric',
  })

  const photoDataUrls: string[] = []
  if (data.images && data.images.length > 0) {
    const urls = data.images.map(img => getImageUrl(img.image_path)).filter(Boolean)
    const results = await Promise.all(urls.map(preloadImage))
    photoDataUrls.push(...results.filter(Boolean))
  }
  const photoUrlsHtml = photoDataUrls.map(src =>
    `<img src="${src}" style="width:90px;height:90px;object-fit:cover;border-radius:8px;border:1px solid #e5e7eb;display:block;" />`
  ).join('')

  const metrics: { title: string; desc: string; score: number | undefined; suffix: string; label: { label: string; color: string; bg: string } }[] = [
    { title: 'LCD', desc: 'Kondisi fisik & fungsional layar', score: data.lcd_input, suffix: '/100', label: conditionLabel(data.lcd_input ?? 0) },
    { title: 'Keyboard', desc: 'Fungsionalitas tuts & responsivitas', score: data.keyboard_input, suffix: '/100', label: conditionLabel(data.keyboard_input ?? 0) },
    { title: 'RAM', desc: 'Kapasitas memori utama', score: data.ram_input, suffix: ' GB', label: ramLabel(data.ram_input ?? 0) },
    { title: 'Baterai', desc: 'Tingkat kesehatan baterai', score: data.battery_input, suffix: '%', label: conditionLabel(data.battery_input ?? 0) },
    { title: 'Processor', desc: data.processor?.name || 'Performa unit pemrosesan', score: data.processor_input, suffix: '', label: conditionLabel(Number(data.processor_input) || 0) },
  ]

  const metricRows = metrics.map((m, i) => {
    const val = m.score != null ? `${m.score}${m.suffix}` : '-'
    const isLast = i === metrics.length - 1
    return `
      <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 20px;${isLast ? '' : 'border-bottom:1px solid #f0f0f0;'}">
        <div style="display:flex;flex-direction:column;gap:2px;">
          <span style="font-weight:700;color:#1a1c1e;font-size:13px;">${m.title}</span>
          <span style="font-size:10px;color:#74777f;">${m.desc}</span>
        </div>
        <div style="display:flex;align-items:center;gap:10px;min-width:120px;justify-content:flex-end;">
          <span style="font-weight:700;color:#1a1c1e;font-size:14px;text-align:right;">${val}</span>
          ${m.score != null ? badge(m.label.label, m.label.bg, m.label.color) : ''}
        </div>
      </div>
    `
  }).join('')

  return `
    <div style="width:800px;padding:32px 40px;font-family:'Inter',Helvetica,Arial,sans-serif;background:#faf9fd;color:#1a1c1e;box-sizing:border-box;">

      <div style="background:#002045;margin:-32px -40px 0 -40px;padding:24px 40px;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <div>
            <p style="font-size:9px;color:#adc7f7;margin:0 0 2px 0;letter-spacing:3px;text-transform:uppercase;font-weight:600;">LapiCheck</p>
            <p style="font-size:20px;font-weight:800;color:#ffffff;margin:0;letter-spacing:-0.3px;">LAPORAN HASIL PENILAIAN</p>
          </div>
          <div style="text-align:right;">
            <p style="font-size:9px;color:#adc7f7;margin:0 0 2px 0;font-weight:600;letter-spacing:1px;text-transform:uppercase;">No. Laporan</p>
            <p style="font-size:14px;font-weight:800;color:#ffffff;margin:0;letter-spacing:0.5px;">#RPT-${String(data.id || '').padStart(4, '0')}</p>
          </div>
        </div>
      </div>

      <div style="display:flex;justify-content:space-between;align-items:flex-start;margin:20px 0 18px 0;padding-bottom:14px;border-bottom:2px solid #002045;">
        <div>
          <p style="font-weight:700;color:#43474e;margin:0 0 4px 0;font-size:9px;text-transform:uppercase;letter-spacing:1.5px;">Perangkat Diperiksa</p>
          <p style="font-weight:800;margin:0;font-size:16px;color:#002045;">${data.laptop_name}</p>
          ${data.customer_name ? `<p style="font-size:10px;color:#74777f;margin:4px 0 0 0;">Customer: <strong>${data.customer_name}</strong></p>` : ''}
          ${data.description ? `<p style="font-size:10px;color:#74777f;margin:4px 0 0 0;font-style:italic;">${data.description}</p>` : ''}
        </div>
        <div style="text-align:right;">
          <p style="font-weight:700;color:#43474e;margin:0 0 4px 0;font-size:9px;text-transform:uppercase;letter-spacing:1.5px;">Tanggal Inspeksi</p>
          <p style="font-weight:700;margin:0;font-size:13px;color:#1a1c1e;">${date}</p>
        </div>
      </div>

      <p style="font-weight:700;font-size:10px;color:#43474e;margin:0 0 10px 0;text-transform:uppercase;letter-spacing:1.5px;">Hasil Pemeriksaan Komponen</p>

      <div style="background:#ffffff;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">
        <div style="background:#f4f3f7;padding:10px 20px;display:flex;justify-content:space-between;font-weight:700;font-size:10px;color:#43474e;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid #e5e7eb;">
          <span>Komponen</span>
          <span>Nilai</span>
        </div>
        ${metricRows}
      </div>

      <div style="margin-top:18px;background:#ffffff;border:1px solid #e5e7eb;border-radius:8px;padding:16px 20px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
          <span style="font-weight:700;font-size:10px;color:#1a1c1e;text-transform:uppercase;letter-spacing:1.5px;">Ringkasan Penilaian</span>
          ${statusBadge(data.status, statusPalette(data.status).text, statusPalette(data.status).bg)}
        </div>
        <div style="display:flex;gap:12px;">
          <div style="flex:1;background:#f4f3f7;padding:14px 12px;border-radius:8px;text-align:center;">
            <p style="font-size:9px;color:#43474e; margin:0 0 6px 0;text-transform:uppercase;letter-spacing:1.5px;font-weight:600;">Skor</p>
            <p style="font-size:18px;font-weight:700;margin:0 0 6px 0;color:#002045;">${data.final_score}
            <span style="font-size:18px;font-weight:600;color:#43474e;">/100</span>
          </div>
          <div style="flex:1;background:#f4f3f7;padding:14px 12px;border-radius:8px;text-align:center;">
            <p style="font-size:9px;color:#43474e;margin:0 0 6px 0; text-transform:uppercase;letter-spacing:1.5px;font-weight:600;">Harga Pasar</p>
            <p style="font-size:18px;font-weight:700;margin:0 0 6px 0;color:#1a1c1e;">${fmt(data.market_price)}</p>
          </div>
          <div style="flex:1;background:#002045;padding:14px 12px;border-radius:8px;text-align:center;">
            <p style="font-size:9px;color:#adc7f7;margin:0 0 6px 0;text-transform:uppercase;letter-spacing:1.5px;font-weight:600;">Estimasi</p>
            <p style="font-size:18px;font-weight:800;margin:0 0 6px 0;color:#ffffff;">${fmt(data.estimated_price)}</p>
          </div>
        </div>
      </div>

      ${photoUrlsHtml ? `
        <div style="margin-top:18px;">
          <p style="font-weight:700;font-size:10px;color:#43474e;margin:0 0 8px 0;text-transform:uppercase;letter-spacing:1.5px;">Dokumentasi</p>
          <div style="display:flex;gap:10px;">${photoUrlsHtml}</div>
        </div>
      ` : ''}

      <div style="margin-top:18px;background:#ffffff;border:1px solid #e5e7eb;border-radius:8px;padding:16px 20px;">
        <p style="font-weight:700;margin:0 0 6px 0;font-size:10px;color:#43474e;text-transform:uppercase;letter-spacing:1.5px;">Kesimpulan</p>
        <p style="font-size:11px;color:#43474e;line-height:1.7;text-align:justify;margin:0;">
          ${data.ai_conclusion || 'Berdasarkan perhitungan Fuzzy Logic, evaluasi telah selesai dilakukan.'}
        </p>
      </div>

      <div style="margin-top:20px;padding-top:12px;border-top:2px solid #002045;text-align:center;">
        <p style="font-size:9px;color:#74777f;margin:0;">Dihasilkan oleh <strong style="color:#002045;">LapiCheck System</strong></p>
      </div>
    </div>
  `
}

export const renderReportToCanvas = async (data: EvaluationData): Promise<HTMLCanvasElement> => {
  const { default: html2canvas } = await import('html2canvas')

  const reportHTML = await buildReportHTML(data)

  const container = document.createElement('div')
  container.innerHTML = reportHTML
  container.style.position = 'absolute'
  container.style.left = '-9999px'
  container.style.top = '0'
  document.body.appendChild(container)

  const canvas = await html2canvas(container, {
    scale: 2,
    useCORS: true,
    backgroundColor: '#faf9fd',
    logging: false,
  })

  document.body.removeChild(container)
  return canvas
}

export const saveCanvasAsPDF = async (canvas: HTMLCanvasElement, laptopName: string, id?: number): Promise<void> => {
  const { default: jspdf } = await import('jspdf')
  const imgData = canvas.toDataURL('image/png')
  const pdf = new jspdf('p', 'mm', 'a4')
  const pw = pdf.internal.pageSize.getWidth()
  const ph = pdf.internal.pageSize.getHeight()
  const iw = pw
  const ih = (canvas.height * iw) / canvas.width

  pdf.addImage(imgData, 'PNG', 0, 0, iw, Math.min(ih, ph))

  const fileName = `LapiCheck_${laptopName.replace(/\s+/g, '_')}_${id || Date.now()}.pdf`
  pdf.save(fileName)
}

export const generatePDF = async (
  _element: HTMLElement,
  data: EvaluationData,
): Promise<void> => {
  const canvas = await renderReportToCanvas(data)
  await saveCanvasAsPDF(canvas, data.laptop_name, data.id)
}
