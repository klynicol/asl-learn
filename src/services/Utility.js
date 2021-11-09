export default class Utility{
    static validateEmail(email) {
        const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }

    static trimObjectStrings(obj){
        Object.keys(obj).map(k => {
            if(typeof obj[k] !== 'string') return obj[k];
            obj[k] = obj[k].trim()
            return obj[k];
        });
    }

    static dateTimeString(date){
        return date.toLocaleString('en-US', { timeStyle: 'short', dateStyle: 'medium'});
    }

    static dateString(date){
        return date.toLocaleString('en-US', {dateStyle: 'medium'});
    }

    static timeStringFull(date){
        return date.toLocaleString('en-US', { timeStyle: 'long'});
    }

    static isSameDate(dateOne, dateTwo){
        if(dateOne.getFullYear() !== dateTwo.getFullYear()){
            return false;
        } else if (dateOne.getMonth() !== dateTwo.getMonth()){
            return false;
        } else if (dateOne.getDate() !== dateTwo.getDate()){
            return false;
        }
        return true;
    }
}

