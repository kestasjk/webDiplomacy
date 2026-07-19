# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

webDiplomacy is a web-based Diplomacy game platform built with PHP and MySQL, featuring both classic web interfaces and a modern React-based frontend. The project supports multiple game variants and includes comprehensive game management, user authentication, and real-time communication features.

## Architecture

### Core Components

- **PHP Backend**: Main application logic written in PHP with MySQL database
- **React Frontend**: Modern UI located in `beta-src/` directory built with TypeScript, Redux Toolkit, and Tailwind CSS
- **SSE Server**: Node.js server for real-time events in `sse-server/` directory
- **Game Engine**: Sophisticated adjudicator system in `gamemaster/` for processing game moves and rules
- **Variant System**: Extensible game variant framework in `variants/` directory

### Key Directories

- `objects/`: Core PHP classes (database, game, user, member objects)
- `gamemaster/`: Game processing engine and adjudicator logic
- `board/`: Game board display and interaction components
- `admin/`: Administrative interface and tools
- `variants/`: Different game variants (Classic, Modern, etc.)
- `lib/`: Shared utility libraries
- `locales/`: Internationalization files
- `beta-src/`: React frontend source code
- `sse-server/`: Server-sent events backend

## Development Commands

### PHP Backend
- **Setup**: Use Docker with `docker-compose up -d` for development
- **Database**: Install using `install/FullInstall/fullInstall.sql`
- **Configuration**: Copy `config.sample.php` to `config.php`
- **Dependencies**: Run `composer update` to install PHP dependencies

### React Frontend (beta-src/)
- **Install**: `npm install`
- **Development**: `npm start` (starts dev server)
- **Build**: `npm run build` (outputs to `../beta` directory)
- **Test**: `npm test`
- **Lint**: Uses ESLint with Airbnb config

### SSE Server
- **Install**: `npm install` in `sse-server/`
- **Run**: `node server.js`

## Deploying to Production

- Production pulls from the `production` branch, which mirrors `master`. To deploy:
  commit to `master`, `git push origin master`, then `git push origin master:production`.
- On each push a GitHub webhook calls `gitpull.php` on the production server, which runs
  `git pull`, rebuilds the beta React app if `beta-src/` changed, and overlays
  `contrib/phpBB3-files/` onto the phpBB install (wiping its compiled cache) if those
  files changed.
- Including `[force-deploy]` in a pushed commit message (an empty commit works:
  `git commit --allow-empty -m "Redeploy [force-deploy]"`) forces the beta rebuild and
  phpBB overlay even when the pull didn't touch those paths.
- To verify: fetch https://webdiplomacy.net/gitpull.txt — it holds the latest deploy run's
  full output and should end with `Deploy finished` and a timestamp. A beta rebuild takes
  a few minutes, so refetch until the marker appears; any npm or copy errors show here.
- Manual/forced deploy (e.g. after a failed build): run `sudo -u www-data php gitpull.php FORCEALL`
  from the production webroot to force the beta rebuild and phpBB overlay without new commits.

## Database Schema

The system uses MySQL with a comprehensive schema including:
- `wD_Users`: User accounts and authentication
- `wD_Games`: Game instances and state
- `wD_Members`: Player participation in games
- `wD_Orders`: Game moves and orders
- `wD_Territories`: Map territories and unit positions

## Key Features

### Game Engine
- **Adjudicator**: Located in `gamemaster/adjudicator/` - handles move validation and conflict resolution
- **Order Processing**: Supports all Diplomacy order types (move, hold, support, convoy)
- **Phase Management**: Handles Spring, Fall, and Winter phases with builds/retreats
- **Variant Support**: Extensible system for different map variants

### User System
- **Authentication**: Supports local accounts, OAuth (Auth0), and SMS verification
- **Permissions**: Role-based system (User, Moderator, Admin)
- **Notifications**: Email and in-app notification system

### Real-time Features
- **SSE**: Server-sent events for live game updates
- **Chat**: Game-specific and global chat systems
- **Push Notifications**: Via SSE

## Testing

### DATC Testing
- Access `/datc.php` for Diplomacy Adjudicator Test Cases
- Batch testing available for comprehensive rule validation
- Test cases ensure adjudicator follows official Diplomacy rules

### Frontend Testing
- React Testing Library setup in `beta-src/`
- Run `npm test` for component testing

## Configuration

### Main Config (`config.php`)
- Database connection settings
- Security secrets (salt, jsonSecret, gameMasterSecret)
- Email configuration
- Cache settings (Redis)
- Debug mode settings

### Environment-specific
- Development: Use Docker setup with `config.sample.php`
- Production: Requires proper security configuration and SSL

## Game Variants

The system supports multiple Diplomacy variants:
- **Classic**: Standard 7-player Diplomacy
- **Modern**: Updated map with modern countries
- **Colonial**: Historical colonial powers
- **World**: Global map variant
- **Custom variants**: Extensible system for new maps

Each variant in `variants/` includes:
- Map data and rendering
- Custom rules and adjudication
- UI modifications
- Installation scripts

## Security Considerations

- All user input is sanitized and validated
- Database uses prepared statements
- Session management with secure tokens
- Admin functions require proper authentication
- Error logs and order logs must be protected from web access

## Development Tips

- Use Docker for consistent development environment
- Follow existing PHP coding conventions
- React frontend uses TypeScript - maintain type safety
- Test moves using DATC test cases
- Use maintenance mode during development/testing
- Monitor game processing with gamemaster status indicators