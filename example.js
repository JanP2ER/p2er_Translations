import i18next from 'i18next' ;

let res = {
    de:{
        translation:{
            key: 'Ein Test',
            test:{
                innen:"Hidden Artifact!"
            }
        }
    },
    en:{
        translation:{
            key: 'Ein Test aber auf Englisch'
        }
    }
};

i18next.init({
    lng:'de',
    fallbackLng:'de',
    //debug: true,
    resources:res
});
let text = i18next.t('key');
console.log(i18next.t('key'));
console.log(i18next.t('key',{lng: "en"}));
console.log(i18next.t('test.innen'));
console.log(i18next.t('test.innen',{lng: "en"})); 