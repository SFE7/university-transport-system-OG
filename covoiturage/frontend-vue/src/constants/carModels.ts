export const CAR_CATEGORIES: Record<string, string[]> = {
  Sedan: ['Peugeot 301', 'Hyundai Accent', 'Mitsubishi Attrage', 'Renault Symbol'],
  'City Hatchback': ['Kia Picanto', 'Hyundai Grand i10', 'Suzuki Celerio', 'Renault Kwid'],
  SUV: ['Chery Tiggo', 'Hyundai Tucson', 'Kia Sportage', 'Dacia Duster'],
  'Compact Car': ['Opel Astra', 'Peugeot 208', 'Citroën C3', 'Volkswagen Golf'],
  Pickup: ['Isuzu D-Max', 'Toyota Hilux', 'Ford Ranger'],
  'Family Van': ['Fiat Doblo', 'Citroën Berlingo', 'Renault Kangoo'],
  Luxury: ['Mercedes C-Class', 'Audi a5', 'Lexus LC', 'BMW X1', 'BMW X2', 'Audi A3'],
}

export const ALL_KNOWN_MODELS = Object.values(CAR_CATEGORIES).flat()
