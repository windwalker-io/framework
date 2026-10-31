依照 repository 的測試 instructions 與 test skill，重整指定範圍內 `packages/*/test/` 的 PHPUnit 測試程式碼風格與結構，包含 test method、lifecycle hook 的命名與呼叫方式。

開始前先閱讀 `.github/instructions/test.instructions.md`、`.github/instructions/cs.instructions.md`、`.github/skills/test/SKILL.md`，並在需要時查閱 `.github/docs/testing.md`。確認使用者指定的 package、檔案或測試範圍；若未指定，先檢視變更或上下文來界定最小合理範圍，不要未經確認就重寫整個 monorepo 的測試。

重構要求：

- 遵循 repo 現有 PHP／PHPUnit 版本與測試慣例；保留 `declare(strict_types=1);`、正確 namespace、imports 與型別宣告。
- 讓測試名稱描述被驗證的行為，採用明確、可搜尋的命名；依 repo 規範使用 `#[Test]`、`#[DataProvider]` 等 attribute，不要只為改名而改變測試涵義。
- lifecycle setup/teardown 優先改用 PHPUnit 的 `#[Before]`、`#[After]`、`#[BeforeClass]`、`#[AfterClass]` 等 attributes，並以能表達實際行為的名稱取代含糊的 `setUp()`／`tearDown()`。
- 遷移 lifecycle hooks 前追查父類別、trait 與繼承的初始化／清理順序；保留必要的 parent 呼叫、執行順序與資源清理。移除空 hook 或無用 fixture 前，先確認不會遮蔽或取代繼承行為。
- 以有意義的行為斷言改善結構；可在多組輸入確實驗證同一行為時採用 data provider。不得為了縮短程式而合併不同情境、削弱 assertions 或改變測試涵義。
- 遵守 instructions 中的 PHPUnit 慣例，例如例外訊息 matcher、mock invocation count matcher，以及 logic-free getter/setter 的測試與 coverage 規則。
- 不改動 production `src/` 行為。若測試支援程式碼確有必要調整，限制在測試支援範圍並說明原因；不得藉測試重構改變公開 API 或 B/C。
- 保留使用者已有的未提交變更；不處理與目標無關的既有問題，不做破壞性清理。

完成時檢查差異，執行修改檔案的 PHP 語法／lint 與格式檢查，並執行能涵蓋改動的最小 PHPUnit selector。若測試需要未配置的資料庫或外部服務，不得假稱通過；依 instructions 嘗試適當的暫時 PHPUnit 設定或 CLI 設定，否則清楚列出未能驗證的範圍、skip 與 incomplete 結果。

成功標準：目標測試結構與命名清晰一致、lifecycle 行為與原測試相同、斷言涵義不被削弱、修改範圍受控，且已報告實際執行的檢查與測試結果。
