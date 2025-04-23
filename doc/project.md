# Test Php Laravel

Implementare una web applica/on laravel* che dopo il login di utente tramite username e password fornisca un token.

Non è necessario la fase di registrazione dell'utente con invio mail, puoi immaginare di par/re con un db già contenente un utenza user: "root" password: "password"

Utilizzare il token recuperato dopo l'accesso per interrogare un api esposta dal sistema che faccia da proxy verso openbrewerydb (https://www.openbrewerydb.org/documentation/) e mostri una lista paginata di birre recuperata dai loro servizi.

**Fornire una pagina web in grado di fare la chiamate verso il backend per poter testare l’api e renderizzare la lista paginata.**

Prestare particolare attenzione alla struttura del codice ed al riuso

E' gradita la presenza di test e l’utilizzo di docker

*in caso non conosca il framework Laravel impiegare o un framework alternativo (per Symfony o PHP nativo o il linguaggio che si padroneggia)