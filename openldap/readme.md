__臺北市教育人員目錄服務__，為臺北市政府教育局所發展教育人員單一身份驗證的 openldap 伺服器，由臺北市老師自主研發，若您為臺北市教師且對程式開發感興趣，請與我聯繫！

這個資料夾內的資料是用來建置一台 openldap server 的 Docker 映像檔，基礎映像檔為 [osixia/openldap](https://github.com/osixia/docker-openldap)，
相關技術文件資料請前往該專案的 Github 頁面查閱。

為了提供臺北市教育人員身份識別資訊的單一存放倉庫，本專案採用臺北市自訂之 schema，新增了 tpeduPerson、tpeduSchool、tpeduSubject 三種物件，其中的資料欄位詳細定義，請參考 [bootstrap/schema](https://github.com/leejoneshane/tpeduSSO/tree/master/openldap/bootstrap/schema) 資料夾，映像檔內的資料為測試資料，僅作為開發測試使用，如果您需要填入自己的測試資料，請參考 [bootstrap/ldif/custom](https://github.com/leejoneshane/tpeduSSO/tree/master/openldap/bootstrap/ldif/custom) 資料夾內之文件。
容器啟動時之相關環境設置，請參考 [my-env.yaml](https://github.com/leejoneshane/tpeduSSO/blob/master/openldap/environment/my_env.yaml) 文件。

## 啟動容器
```
docker run -p 389:389 -p 636:636 -d leejoneshane/tpedusso:openldap
```

您可以使用底下指令測試伺服器是否正常運行，如果您看到伺服器回應 ldif 文件，即代表伺服器正在運行中。
```
ldapsearch -H "ldap://127.0.0.1" -D "cn=admin,dc=tp,dc=edu,dc=tw" -w test -b "dc=tp,dc=edu,dc=tw"
```

您可以把這個執行中的容器，當成 [臺北市教育人員單一身份驗證服務平台](https://ldap.tp.edu.tw) 的後端資料庫，以便於開發程式的過程中，進行各種測試。

## 建置映像檔

如果您有自行建置伺服器的需求，請先安裝 docker 在您的伺服器上，並修改 Dockerfile 文件，然後再執行底下指令：
```
docker build .
```

本專案所有程式碼開源，歡迎各縣市網路中心自行下載使用。
