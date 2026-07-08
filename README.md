## プロジェクト名
COACHTECH お問い合わせフォーム

## 概要
プロジェクトの目的と実装機能の概要説明

## ER図
```mermaid
erDiagram
    CATEGORY ||--o{ CONTACT : hasMany
    CONTACT }o--o{ TAG : belongsToMany

    CATEGORY {
        bigint id PK
        string name
    }

    CONTACT {
        bigint id PK
        bigint category_id FK
        string name
        string email
        string subject
        text message
    }

    TAG {
        bigint id PK
        string name
    }

    CONTACT_TAG {
        bigint contact_id FK
        bigint tag_id FK
    }

    CONTACT ||--o{ CONTACT_TAG : has
    TAG ||--o{ CONTACT_TAG : has
```

## 環境構築手順

## 使用技術
- Laravel10.4
- MySQL 8.0
- Nginx
- Docker
- phpMyAdmin

## APIエンドポイント一覧

## 開発環境URL
http://localhost

## 作成者
浅井 明日香