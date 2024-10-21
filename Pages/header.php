<style>
    /* Style commun pour tous les écrans */
    header {
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
        height: 100px;
        background-color: #f0f0f0;
    }

    #logo {
        width: 200px;
        height: auto;
    }

    .btn-logout {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: red;
        color: white;
        border: none;
        padding: 10px 20px;
        cursor: pointer;
        border-radius: 5px;
        font-size: 16px;
    }

    .btn-logout:hover {
        background-color: darkred;
    }

    .error-message {
        color: red;
    }

    /* Media query pour les écrans de taille inférieure à 768px (tablettes et téléphones) */
    @media (max-width: 768px) {
        header {
            flex-direction: column;
            height: auto;
            padding: 10px;
        }

        #logo {
            width: 150px;
        }

        .btn-logout {
            position: relative;
            margin-top: 10px;
            top: 0;
            right: 0;
            padding: 8px 16px;
            font-size: 14px;
        }
    }

    /* Media query pour les écrans de taille inférieure à 480px (petits téléphones) */
    @media (max-width: 480px) {
        #logo {
            width: 120px;
        }

        .btn-logout {
            padding: 6px 12px;
            font-size: 12px;
        }
    }
</style>


<header>
    <img src='../Assets/logo.svg' alt='Logo Institut du Savoir' id='logo'>
    <form action="logout.php" method="post" style="display:inline;">
        <button type="submit" class="btn-logout">Déconnexion</button>
    </form>
</header>
