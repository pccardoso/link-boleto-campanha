import axios from 'axios';

export const getAllAuthorized = async () =>  {

    try{

        const response = await axios.get('/authorized-plates');
        return response.data;

    }catch(error){
        throw error;
        
    }

}