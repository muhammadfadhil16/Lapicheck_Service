export type ScoreOption = {
  value: number
  label: string
}

export type CurrencyOption = {
  code: string
  country: string
  locale: string
  symbol: string
  placeholder: string
  flagClass: string
}

export type Processor = {
  id: number
  name: string
  benchmark_score: number
  category: string
}

export type LaptopBrand = { id: number; name: string; laptops_count?: number }

export type Laptop = {
  id: number
  model_name: string
  processor_name: string
  benchmark_score: number
  category: string
  market_price?: number
  price_month?: number
  price_year?: number
  price_updated_at?: string
  brand: LaptopBrand
}

export type MetricKey = 'LCD' | 'KesehatanBaterai' | 'Processor' | 'KondisiKeyboard' | 'RAM'

export type MetricRule = {
  rendah: [number, number]
  normal?: [number, number, number]
  tinggi: [number, number]
}

export type MetricValueField =
  | 'lcd_input'
  | 'battery_input'
  | 'processor_input'
  | 'keyboard_input'
  | 'ram_input'

export type MetricDefinition = {
  id: string
  label: string
  icon: string
  metricKey: MetricKey
  field: MetricValueField
  widthMode: 'percent' | 'full'
  valueSuffix?: string
}

export type StatusColorRule = {
  color: string
  keywords: string[]
  exclude?: string[]
}

export const STATUS_COLOR_RULES: StatusColorRule[] = [
  { color: '#047857', keywords: ['layak', 'sangat layak'], exclude: ['tidak', 'cukup'] },
  { color: '#b45309', keywords: ['cukup', 'cukup layak'] },
  { color: '#be123c', keywords: ['tidak', 'tidak layak', 'buruk'] },
]

export const DEFAULT_STATUS_COLOR = '#1a1c1e'
export const DEFAULT_METRIC_COLOR = DEFAULT_STATUS_COLOR

export const LCD_OPTIONS: ScoreOption[] = [
  { value: 100, label: 'Sangat Baik — gambar jernih, tanpa garis, bercak, atau kedip' },
  { value: 80, label: 'Cukup Baik — cacat ringan, tampilan masih cukup jelas' },
  { value: 60, label: 'Kurang Baik — shadow, burn-in, dead pixel, atau garis ringan' },
  { value: 0, label: 'Buruk — retak, bocor, flicker parah, atau layar mati' },
]

export const KEYBOARD_OPTIONS: ScoreOption[] = [
  { value: 100, label: 'Sangat Baik — seluruh tombol responsif dan berfungsi' },
  { value: 80, label: 'Cukup Baik — fisik aus atau 1–2 tombol kurang nyaman' },
  { value: 60, label: 'Kurang Baik — 1–2 tombol fungsi sekunder tidak berfungsi' },
  { value: 0, label: 'Buruk — tombol utama mati, ghosting, atau korslet' },
]

export const RAM_OPTIONS: ScoreOption[] = [
  { value: 4, label: '4 GB (Kategori: Rendah)' },
  { value: 8, label: '8 GB (Kategori: Sedang)' },
  { value: 12, label: '12 GB (Kategori: Tinggi)' },
  { value: 16, label: '16 GB (Kategori: Tinggi)' },
  { value: 32, label: '32 GB (Kategori: Tinggi)' },
]

export const DEFAULT_CURRENCY: CurrencyOption = {
  code: 'IDR',
  country: 'Indonesia',
  locale: 'id-ID',
  symbol: 'Rp',
  placeholder: 'Rp8.500.000',
  flagClass: 'bg-[linear-gradient(180deg,#dc2626_0_50%,#ffffff_50%_100%)]',
}

export const CURRENCY_OPTIONS: CurrencyOption[] = [
  DEFAULT_CURRENCY,
  {
    code: 'MYR',
    country: 'Malaysia',
    locale: 'ms-MY',
    symbol: 'RM',
    placeholder: 'RM2,400',
    flagClass: 'bg-[linear-gradient(180deg,#ef4444_0_33%,#ffffff_33%_66%,#2563eb_66%_100%)]',
  },
  {
    code: 'SGD',
    country: 'Singapore',
    locale: 'en-SG',
    symbol: 'S$',
    placeholder: 'S$1,200',
    flagClass: 'bg-[linear-gradient(180deg,#ef4444_0_50%,#ffffff_50%_100%)]',
  },
  {
    code: 'EUR',
    country: 'Eurozone',
    locale: 'de-DE',
    symbol: 'EUR',
    placeholder: 'EUR1.500',
    flagClass: 'bg-[#1d4ed8]',
  },
  {
    code: 'GBP',
    country: 'United Kingdom',
    locale: 'en-GB',
    symbol: 'GBP',
    placeholder: 'GBP1,100',
    flagClass: 'bg-[#1e3a8a]',
  },
  {
    code: 'USD',
    country: 'United States',
    locale: 'en-US',
    symbol: '$',
    placeholder: '$750',
    flagClass: 'bg-[linear-gradient(180deg,#dc2626_0_50%,#ffffff_50%_100%)]',
  },
  {
    code: 'JPY',
    country: 'Japan',
    locale: 'ja-JP',
    symbol: '¥',
    placeholder: '¥110,000',
    flagClass: 'bg-white bg-[radial-gradient(circle_at_50%_50%,#dc2626_0_45%,transparent_46%)]',
  },
  {
    code: 'CAD',
    country: 'Canada',
    locale: 'en-CA',
    symbol: 'CAD',
    placeholder: 'CAD950',
    flagClass: 'bg-[linear-gradient(90deg,#dc2626_0_25%,#ffffff_25%_75%,#dc2626_75%_100%)]',
  },
]

export const METRIC_RULES: Record<MetricKey, MetricRule> = {
  LCD: { rendah: [0, 54], normal: [55, 65, 75], tinggi: [76, 100] },
  KondisiKeyboard: { rendah: [0, 54], normal: [55, 65, 75], tinggi: [76, 100] },
  KesehatanBaterai: { rendah: [0, 64], normal: [65, 70, 75], tinggi: [76, 100] },
  RAM: { rendah: [0, 7], normal: [8, 10, 12], tinggi: [13, 128] },
  Processor: { rendah: [0, 7999], normal: [8000, 13000, 18000], tinggi: [18001, 40000] },
}

export const METRIC_DEFINITIONS: MetricDefinition[] = [
  {
    id: 'lcd',
    label: 'LCD',
    icon: 'monitor',
    metricKey: 'LCD',
    field: 'lcd_input',
    widthMode: 'percent',
    valueSuffix: '%',
  },
  {
    id: 'battery',
    label: 'Baterai',
    icon: 'battery_std',
    metricKey: 'KesehatanBaterai',
    field: 'battery_input',
    widthMode: 'percent',
    valueSuffix: '%',
  },
  {
    id: 'ram',
    label: 'RAM',
    icon: 'memory',
    metricKey: 'RAM',
    field: 'ram_input',
    widthMode: 'percent',
    valueSuffix: ' GB',
  },
  {
    id: 'processor',
    label: 'Processor',
    icon: 'speed',
    metricKey: 'Processor',
    field: 'processor_input',
    widthMode: 'full',
  },
  {
    id: 'keyboard',
    label: 'Keyboard',
    icon: 'keyboard',
    metricKey: 'KondisiKeyboard',
    field: 'keyboard_input',
    widthMode: 'percent',
    valueSuffix: '%',
  },
]

export const currencyLabel = (currency: CurrencyOption) => {
  return `${currency.symbol} (${currency.code})`
}
