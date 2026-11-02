> Draft: to be merged into the official docs.

## Arr::sync()

比對「現有資料」與「傳入資料」,拆成 `creating` / `deleting` / `keeping` 三組,讓外層決定如何新增、刪除、更新。

```php
Arr::sync(
    iterable $existing,
    iterable $incoming,
    string|callable|null $by = null,
): array{ creating: array, deleting: array, keeping: array }
```

### 回傳

| 群組       | 內容                                           | 資料來源   |
|----------|----------------------------------------------|--------|
| `creating` | 只存在於 `$incoming` 的項目(需新增)                    | incoming |
| `deleting` | 只存在於 `$existing` 的項目(需刪除)                    | existing |
| `keeping`  | 兩邊都有的項目(需更新),內容為 **incoming** 的資料        | incoming |

- 三組都保留來源陣列的**原鍵**。
- `keeping` 不含 existing 資料;需要時請用比對值自行取回(如 `array_column($existing, null, 'id')`)。
- 比對值重複時,後出現者覆蓋前者。

### `$by` 比對方式

```php
// 1. null:以陣列 key 比對
Arr::sync(['a' => 1, 'b' => 2], ['b' => 20, 'c' => 30]);
// creating: ['c' => 30], deleting: ['a' => 1], keeping: ['b' => 20]

// 2. string:以欄位名稱比對(陣列、物件皆可,支援 path 如 'user.id')
Arr::sync($rows, $input, 'id');

// 3. callable:fn($item, $key) 回傳比對值
Arr::sync($rows, $input, fn($item, $key) => $item['type'] . ':' . $item['id']);
```

### 支援的比對值

- `int` / `string`
- `BackedEnum`(用 `value`)、`UnitEnum`(用 `name`)
- `Stringable`(轉字串)
- 其他物件(以 identity 比對)

其他型別(包含 `null`,例如欄位不存在)會丟出 `InvalidArgumentException`。

### 範例

```php
$existing = [10 => ['id' => 1, 'name' => 'PHP'], 11 => ['id' => 2, 'name' => 'Old']];
$incoming = [5 => ['id' => 2, 'name' => 'New'], 6 => ['id' => 3, 'name' => 'Go']];

['creating' => $creating, 'deleting' => $deleting, 'keeping' => $keeping]
    = Arr::sync($existing, $incoming, 'id');

// creating: [6 => ['id' => 3, 'name' => 'Go']]
// deleting: [10 => ['id' => 1, 'name' => 'PHP']]
// keeping:  [5 => ['id' => 2, 'name' => 'New']]

$existingById = array_column($existing, null, 'id');

foreach ($keeping as $item) {
    $old = $existingById[$item['id']];
    // update($old, $item);
}
```
