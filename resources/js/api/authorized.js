import axios from 'axios';

export const getAllAuthorized = async () =>  {

    try{

        const response = await axios.get('/authorized-plates');
        return response.data;

    }catch(error){
        throw error;
        
    }

}

export const getAllAuthorizedWithHistory = async () =>  {

    try{

        const response = await axios.get('/api/authorized-with-history');
        return response.data;

    }catch(error){
        throw error;
        
    }

}