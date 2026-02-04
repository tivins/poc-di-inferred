# dependency-injection

PoC d’**injection de dépendances automatique** en PHP : le conteneur instancie une classe en résolvant récursivement les types du constructeur (reflection) et en respectant les bindings interface → implémentation.

## Fonctionnement

- **Container** : `get($class)` crée l’instance (singleton), résout les paramètres du constructeur via le **ClassAnalyzer**, et utilise les **bindings** pour les interfaces.
- **ClassAnalyzer** : analyse le constructeur (paramètres typés) et met en cache le résultat (invalidation par date de modification du fichier source).
- **Cache** : interface `CacheInterface` avec implémentations `CacheFile`, `CacheMemory`, `CacheRedis` (cache optionnel pour les analyses).

## Prérequis

- PHP 8.3+
- Composer

## Installation

```bash
composer install
```

## Utilisation

```php
use Tivins\DI\Core\Container;
use Tivins\DI\Core\ClassAnalyzer;
use Tivins\DI\Infrastructure\CacheFile; // ou CacheMemory

$container = new Container(new ClassAnalyzer(new CacheFile(__DIR__ . '/.di/cache')));
$container->bind(RegistryInterface::class, Registry::class);

$app = $container->get(Application::class); // Application reçoit RegistryInterface (Registry) automatiquement
```

Exemple complet et scénarios (cache, singleton, `remove`) : voir `test.php`.

## Structure

```
src/DI/
├── Core/
│   ├── CacheInterface.php
│   ├── ClassAnalyzer.php
│   └── Container.php
└── Infrastructure/
    ├── CacheFile.php
    ├── CacheMemory.php
    └── CacheRedis.php
```

## Licence

MIT
