{% $phpOpen %}

declare(strict_types=1);

namespace App\Migration;

use Windwalker\Core\Migration\AbstractMigration;
use Windwalker\Core\Migration\MigrateUp;
use Windwalker\Core\Migration\MigrateDown;
use Windwalker\Database\Schema\Schema;

return new /** {% $version %}_{% $name %} */ class extends AbstractMigration {
    #[MigrateUp]
    public function up(): void
    {
        $this->createTable(
            'cache_items',
            function (Schema $schema) {
                $schema->primaryBigint('id');
                $schema->varchar('key');
                $schema->varchar('group');
                $schema->longtext('payload');
                $schema->integer('expired_at')->nullable(true);

                $schema->addUniqueKey(['key', 'group']);
                $schema->addIndex('expired_at');
            }
        );
    }

    #[MigrateDown]
    public function down(): void
    {
        $this->dropTables('cache_items');
    }
};
