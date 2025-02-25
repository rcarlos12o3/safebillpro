const CompanyDefault = {
    subdomain: null,
    sale: {
        status: null,
        period: null,
        ticket: null,
    },
    purchase: {
        status: null,
        period: null,
        ticket: null,
    },
}

export function getLocalStorage(key) {
    try {
        const storedData = localStorage.getItem(key);
        return storedData ? JSON.parse(storedData) : null;
    } catch (error) {
        console.error('Error al intentar obtener datos desde localStorage:', error);
        return null;
    }
}

export function setLocalStorage(key, data) {
    try {
        const serializedData = JSON.stringify(data);
        localStorage.setItem(key, serializedData);
    } catch (error) {
        console.error('Error al intentar almacenar en localStorage:', error);
    }
}

export function getSubdomain() {
    const host = window.location.host;
    const parts = host.split('.');
    return parts.length >= 2 ? parts[0] : null;
}

export function setFirstDataCompany(dataSire) {
    const subdomain = getSubdomain();
    const company = { ...CompanyDefault, subdomain };
    dataSire.push(company);
    setLocalStorage('sire', dataSire);
}

export function setSireStorage() {
    const subdomain = getSubdomain();
    let sire = getLocalStorage('sire') || [];

    const company = sire.find(co => co.subdomain === subdomain);

    if (!company) {
        setFirstDataCompany(sire);
    }
}

export function updateFieldToCompany(type, field, value) {
    const subdomain = getSubdomain();
    const sire = getLocalStorage('sire') || [];

    const updatedSire = sire.map(company => {
        if (company.subdomain === subdomain) {
            return {
                ...company,
                [type]: {
                    ...company[type],
                    [field]: value,
                },
            };
        }
        return company;
    });

    setLocalStorage('sire', updatedSire);
}

export function getStorageSireCompany() {
    const subdomain = getSubdomain();
    const sire = getLocalStorage('sire') || [];
    return sire.find(co => co.subdomain === subdomain);
}

// export function updateLocalStorage(key, updateFunction) {
//     try {
//         // Obtener datos existentes desde localStorage
//         const existingData = getLocalStorage(key) || {};

//         // Aplicar la función de actualización a los datos existentes
//         updateFunction(existingData);

//         // Almacenar los datos actualizados en localStorage
//         localStorage.setItem(key, JSON.stringify(existingData));
//     } catch (error) {
//         console.error('Error al intentar actualizar datos en localStorage:', error);
//     }
// }

// export function updateDataSire(company, type, field, value) {
//     const sireData = getLocalStorage('sire') || {};
//     const company = sireData.find(e => e.company == company)
// }

// // Método para obtener datos desde localStorage con valor por defecto
// export function getLocalStorageWithDefault(key, defaultValue) {
//     try {
//         const storedData = localStorage.getItem(key);
//         return storedData ? JSON.parse(storedData) : defaultValue;
//     } catch (error) {
//         console.error('Error al intentar obtener datos desde localStorage:', error);
//         return defaultValue;
//     }
// }

// // Método para actualizar datos en localStorage
// export function updateLocalStorage(key, updateFunction) {
//     try {
//         // Obtener datos existentes desde localStorage con un valor por defecto
//         const existingData = getLocalStorageWithDefault(key, {
//             company: null,
//             sale: {
//                 status: null,
//                 period: null,
//                 ticket: null,
//             },
//             purchase: {
//                 status: null,
//                 period: null,
//                 ticket: null,
//             },
//         });

//         // Aplicar la función de actualización a los datos existentes
//         updateFunction(existingData);

//         // Almacenar los datos actualizados en localStorage
//         localStorage.setItem(key, JSON.stringify(existingData));
//     } catch (error) {
//         console.error('Error al intentar actualizar datos en localStorage:', error);
//     }
// }