# DearTime Insurance Platform

[![License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

DearTime Insurance is Malaysia's first open-source digital life insurance platform. This repository contains the core platform source code that powers DearTime's innovative insurance services.

## Overview

DearTime is revolutionizing the insurance industry in Malaysia with a fully digital approach to life insurance. This platform is designed to streamline the insurance process, making it more accessible, transparent, and user-friendly for all Malaysians.

## Key Features

- **Digital-First Insurance Experience**: Complete end-to-end digital journey from application to policy management
- **Paperless Application Process**: Environmentally friendly approach with no physical paperwork
- **Real-Time Policy Management**: Instant updates and access to policy information
- **Automated Underwriting**: Efficient risk assessment and policy issuance
- **Secure Payment Gateway**: Multiple payment options with enhanced security
- **Customer Portal**: Self-service dashboard for customers to manage their policies
- **Agent/Broker Portal**: Tools for insurance professionals to manage client policies
- **Claims Processing System**: Streamlined digital claims submission and processing
- **Reporting & Analytics**: Comprehensive data visualization and reporting tools
- **API Integration**: Connectivity with third-party services and systems

## Technology Stack

- **Backend**: Laravel PHP Framework
- **Database**: MySQL
- **Frontend**: Vue.js/Blade Templates
- **API**: RESTful architecture
- **Authentication**: JWT-based secure authentication
- **Deployment**: Docker containerization

## Installation

### Prerequisites
- PHP >= 7.4
- Composer
- MySQL >= 5.7
- Node.js and NPM

### Setup Instructions
1. Clone the repository:
```bash
git clone https://github.com/deartime/insurance-platform.git
cd insurance-platform
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install JavaScript dependencies:
```bash
npm install && npm run dev
```

4. Set up environment variables:
```bash
cp .env.example .env
php artisan key:generate
```

5. Configure database settings in `.env` file

6. Run migrations and seeders:
```bash
php artisan migrate --seed
```

7. Start the development server:
```bash
php artisan serve
```

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## About

### DearTime

[DearTime](https://deartime.com) is a digital life insurance provider in Malaysia, committed to making insurance more accessible and transparent for all Malaysians through innovative technology.

### Areca Capital

[Areca Capital](https://arecacapital.com) is the main investor behind DearTime, supporting the vision of transforming the Malaysian insurance landscape through digital innovation.

## Acknowledgments

DearTime Insurance Platform is proud to be Malaysia's first open-source digital life insurance core platform. This initiative represents a significant step toward greater transparency and innovation in the insurance industry.