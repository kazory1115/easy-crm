# 標準化開發工作流程 (Standardized Development Workflow)

本文件旨在定義 Easy CRM 專案從需求發想到功能上線的標準化流程。此流程的目標是提高團隊協作效率、確保交付品質，並讓專案能以可預測的節奏持續推進。

我們採用基於 **Git Flow** 精神的 **Feature Branch Workflow**。

---

## 核心分支 (Core Branches)

- **`main`**:
    - **用途:** 永遠處於「可上線 (Production-Ready)」的狀態。此分支的內容應與目前生產環境的程式碼一致。
    - **規則:**
        - **不允許** 直接推送 (Push) 任何 Commit。
        - 只能從 `develop` 或 `hotfix` 分支合併 (Merge)。

- **`develop`**:
    - **用途:** 主要的開發分支，匯集所有已完成並測試過的功能。這是下一個版本的預備發布分支。
    - **規則:**
        - 當開發新功能時，從 `develop` 分支出去。
        - 當功能開發完成後，合併回 `develop`。

---

## 開發流程六步驟 (The 6-Step Workflow)

### 步驟 1: 需求確認與任務拆分 (Planning)

- **執行者:** PM, Tech Lead, 開發團隊
- **活動:**
    1. PM 在專案管理工具 (如 Jira, Trello) 上建立一個新的功能卡片 (Ticket/Card)，詳細描述其 **使用者故事 (User Story)**、**規格 (Specs)** 與 **驗收條件 (Acceptance Criteria)**。
    2. Tech Lead 與開發者根據需求，進行技術討論，將大功能拆分為數個可執行的後端與前端子任務。

### 步驟 2: 建立功能分支 (Branching)

- **執行者:** 開發者 (Developer)
- **活動:**
    1. 切換到最新的 `develop` 分支:
       ```bash
       git checkout develop
       git pull origin develop
       ```
    2. 根據功能建立一個新的 `feature` 分支。分支命名應清晰易懂，格式為 `feature/[功能簡述]`。
       ```bash
       # 範例：開發客戶搜尋功能
       git checkout -b feature/customer-search
       ```

### 步驟 3: 開發與本地測試 (Implementation)

- **執行者:** 開發者 (Developer)
- **活動:**
    1. 在 `feature` 分支上進行程式碼開發。
    2. **後端:** 撰寫 API 邏輯與對應的 **單元測試 (Unit Test)** 或 **功能測試 (Feature Test)**。
       ```bash
       # 在 Laravel 專案中執行測試
       php artisan test
       ```
    3. **前端:** 開發 Vue 元件，串接 API，並確保在本地開發環境 (`npm run dev`) 中功能正常。
    4. 保持 Commit 的原子性，一個 Commit 專注做一件事情，並撰寫清晰的 Commit Message。

### 步驟 4: 程式碼審查 (Code Review)

- **執行者:** Tech Lead, 開發者 (Developer)
- **活動:**
    1. 當功能開發完成後，將 `feature` 分支推送到遠端 (GitLab/GitHub)。
       ```bash
       git push -u origin feature/customer-search
       ```
    2. 在 GitLab/GitHub 上建立一個 **合併請求 (Merge Request / Pull Request, MR/PR)**，目標分支為 `develop`。
    3. 在 MR/PR 的描述中，簡述此功能的目的、改動範圍，並附上相關的 Ticket 連結。
    4. 指派 Tech Lead 或其他資深同事進行 Code Review。
    5. Reviewer 針對程式碼的 **可讀性、效能、架構、安全性** 等方面提出建議。
    6. 開發者根據回饋修改程式碼，直到 MR/PR 被核准 (Approve)。

### 步驟 5: 合併與整合測試 (Merge & QA)

- **執行者:** Tech Lead, QA
- **活動:**
    1. MR/PR 核准後，由 Tech Lead 或開發者將 `feature` 分支合併至 `develop` 分支。建議使用 "Squash and Merge" 來保持 `develop` 分支的整潔。
    2. 程式碼合併後，自動化部署工具 (CI/CD) 會將 `develop` 分支的最新版本部署到 **測試環境 (Staging/Testing Environment)**。
    3. QA 在測試環境中，根據步驟 1 的驗收條件，對功能進行完整的測試。
    4. 如果發現 Bug，QA 會回報問題，由開發者建立新的分支進行修復。

### 步驟 6: 版本發布 (Release)

- **執行者:** Tech Lead
- **活動:**
    1. 當 `develop` 分支累積了足夠的功能，並通過所有測試後，準備進行版本發布。
    2. 從 `develop` 分支建立一個 `release` 分支 (如 `release/v1.2.0`)。在此分支上只做版本號修改、文件更新等小調整。
    3. 將 `release` 分支同時合併到 `main` 和 `develop` 分支。
    4. 對 `main` 分支上對應的 Commit 打上版本標籤 (Tag)。
       ```bash
       git checkout main
       git pull origin main
       git tag -a v1.2.0 -m "Version 1.2.0"
       git push origin v1.2.0
       ```
    5. CI/CD 工具會自動將 `main` 分支的最新版本部署到 **生產環境 (Production Environment)**。

---

## 緊急修復 (Hotfix)

如果生產環境發生緊急問題，需要立即修復：
1. 從 `main` 分支建立 `hotfix` 分支 (如 `hotfix/fix-login-error`)。
2. 修復問題並提交。
3. 將 `hotfix` 分支同時合併回 `main` 和 `develop` 分支，確保修復同步到開發流程中。
